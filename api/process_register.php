<?php
ob_start();

function hirevo_register_shutdown_handler() {
    $lastError = error_get_last();
    if (!$lastError) {
        return;
    }

    $fatalTypes = array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR);
    if (!in_array($lastError['type'], $fatalTypes)) {
        return;
    }

    error_log('HireVO register fatal: ' . $lastError['message'] . ' in ' . $lastError['file'] . ':' . $lastError['line']);

    if (!headers_sent()) {
        if (function_exists('http_response_code')) {
            http_response_code(500);
        } else {
            header('HTTP/1.1 500 Internal Server Error');
        }
        header('Content-Type: application/json');
    }

    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    echo json_encode(array(
        'success' => false,
        'message' => 'Server fatal error in register API.',
        'fatal' => $lastError['message'],
        'file' => basename($lastError['file']),
        'line' => $lastError['line']
    ));
}

register_shutdown_function('hirevo_register_shutdown_handler');

if (session_id() === '') {
    @session_start();
}

if (!headers_sent()) {
    header('Content-Type: application/json');
}

// Include database configuration
require_once(dirname(__FILE__) . '/../config/db.php');

// Get POST data
$data = json_decode(file_get_contents("php://input"), true);
if (!is_array($data)) {
    $data = array();
}

/**
 * Generate a prefixed user_id based on role.
 * Employer => EMP-001, EMP-002, ...
 * Job Seeker => JOB-001, JOB-002, ...
 */
function generateUserId($conn, $role) {
    $prefix = '';
    if ($role === 'employer') {
        $prefix = 'EMP';
    } elseif ($role === 'job_seeker') {
        $prefix = 'JOB';
    } else {
        $prefix = 'USR';
    }

    // Find the highest existing number for this prefix
    $pattern = $prefix . '-%';
    $stmt = mysqli_prepare($conn, "SELECT user_id FROM users WHERE user_id LIKE ? ORDER BY user_id DESC LIMIT 1");
    if (!$stmt) {
        return $prefix . '-001';
    }

    mysqli_stmt_bind_param($stmt, 's', $pattern);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $latestUserId);

    if (mysqli_stmt_fetch($stmt)) {
        // Extract the numeric part and increment
        $lastNum = (int) substr($latestUserId, strlen($prefix) + 1);
        $nextNum = $lastNum + 1;
    } else {
        $nextNum = 1;
    }

    mysqli_stmt_close($stmt);

    return $prefix . '-' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
}

/**
 * Normalize role values from UI/API variants to DB enum format.
 */
function normalizeRole($role) {
    $role = trim((string) $role);
    if ($role === 'job-seeker') {
        return 'job_seeker';
    }
    return $role;
}

/**
 * Insert the role-specific profile row.
 * Uses ON DUPLICATE KEY UPDATE to stay idempotent on retries.
 */
function createRoleProfile($conn, $role, $userId, $data, $fullName) {
    if ($role === 'employer') {
        $companyName = sanitize(isset($data['companyName']) ? $data['companyName'] : '', $conn);
        $companyEmail = sanitize(isset($data['companyEmail']) ? $data['companyEmail'] : '', $conn);
        $companySize = sanitize(isset($data['companySize']) ? $data['companySize'] : '', $conn);
        $industry = sanitize(isset($data['industry']) ? $data['industry'] : '', $conn);

        if (empty($companyName) || empty($companySize) || empty($industry)) {
            throw new Exception('Company details are required for employer registration');
        }

        $companyDesc = '';
        $companyLocation = !empty($data['companyLocation']) ? sanitize($data['companyLocation'], $conn) : 'Not specified';
        $companyContact = !empty($companyEmail) ? $companyEmail : 'Not specified';

        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO employers (user_id, company_name, company_description, industry, company_size, location, contactNum)
             VALUES (?, ?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE user_id = user_id"
        );
        if (!$stmt) {
            throw new Exception('Database error creating employer profile');
        }

        mysqli_stmt_bind_param($stmt, 'sssssss', $userId, $companyName, $companyDesc, $industry, $companySize, $companyLocation, $companyContact);
        if (!mysqli_stmt_execute($stmt)) {
            $err = mysqli_stmt_error($stmt);
            mysqli_stmt_close($stmt);
            throw new Exception('Error creating employer profile: ' . $err);
        }
        mysqli_stmt_close($stmt);
        return;
    }

    if ($role === 'job_seeker') {
        $nameParts = explode(' ', $fullName, 2);
        $firstName = sanitize($nameParts[0], $conn);
        $lastName = sanitize(isset($nameParts[1]) ? $nameParts[1] : '', $conn);
        $phone = sanitize(isset($data['phone']) ? $data['phone'] : '', $conn);
        $location = sanitize(isset($data['location']) ? $data['location'] : '', $conn);
        $experience = sanitize(isset($data['experience']) ? $data['experience'] : '', $conn);

        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO job_seekers (user_id, firstName, lastName, contactNum, address, experience)
             VALUES (?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE user_id = user_id"
        );
        if (!$stmt) {
            throw new Exception('Database error creating job seeker profile');
        }

        mysqli_stmt_bind_param($stmt, 'ssssss', $userId, $firstName, $lastName, $phone, $location, $experience);
        if (!mysqli_stmt_execute($stmt)) {
            $err = mysqli_stmt_error($stmt);
            mysqli_stmt_close($stmt);
            throw new Exception('Error creating job seeker profile: ' . $err);
        }
        mysqli_stmt_close($stmt);
        return;
    }

    throw new Exception('Invalid role selected');
}

// Handle registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($data['action']) && $data['action'] === 'register') {
    $role = normalizeRole(sanitize(isset($data['role']) ? $data['role'] : '', $conn));
    $fullName = sanitize(isset($data['fullName']) ? $data['fullName'] : '', $conn);
    $email = sanitize(isset($data['email']) ? $data['email'] : '', $conn);
    $password = isset($data['password']) ? $data['password'] : '';
    $confirmPassword = isset($data['confirmPassword']) ? $data['confirmPassword'] : '';

    // Validation
    if (empty($role) || empty($fullName) || empty($email) || empty($password)) {
        echo json_encode(array('success' => false, 'message' => 'All required fields must be filled'));
        exit;
    }

    if (!in_array($role, array('employer', 'job_seeker'))) {
        echo json_encode(array('success' => false, 'message' => 'Invalid role selected'));
        exit;
    }

    if (!validateEmail($email)) {
        echo json_encode(array('success' => false, 'message' => 'Invalid email format'));
        exit;
    }

    if ($password !== $confirmPassword) {
        echo json_encode(array('success' => false, 'message' => 'Passwords do not match'));
        exit;
    }

    if (strlen($password) < 6) {
        echo json_encode(array('success' => false, 'message' => 'Password must be at least 6 characters'));
        exit;
    }

    // Check if email already exists (prepared statement)
    $stmt = mysqli_prepare($conn, "SELECT COUNT(*) FROM users WHERE email = ?");
    if (!$stmt) {
        echo json_encode(array('success' => false, 'message' => 'Database error'));
        exit;
    }

    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $existingCount);
    mysqli_stmt_fetch($stmt);

    if ((int) $existingCount > 0) {
        echo json_encode(array('success' => false, 'message' => 'Email already registered'));
        mysqli_stmt_close($stmt);
        exit;
    }
    mysqli_stmt_close($stmt);

    // Generate username from email
    $username = sanitize(explode('@', $email)[0], $conn);

    // Hash password using bcrypt
    $hashedPassword = hashPassword($password);

    // Generate the prefixed user_id
    $user_id = generateUserId($conn, $role);

    // Start transaction with broad shared-host compatibility.
    $useTransaction = false;
    if (function_exists('mysqli_autocommit')) {
        $useTransaction = @mysqli_autocommit($conn, false);
    }

    try {
        // Insert user; retry a few times if generated user_id collides.
        $createdUser = false;
        $attempts = 0;
        while (!$createdUser && $attempts < 5) {
            $attempts++;
            $user_id = generateUserId($conn, $role);

            $stmt = mysqli_prepare($conn, "INSERT INTO users (user_id, username, email, password, role) VALUES (?, ?, ?, ?, ?)");
            if (!$stmt) {
                throw new Exception('Database error creating user');
            }

            mysqli_stmt_bind_param($stmt, 'sssss', $user_id, $username, $email, $hashedPassword, $role);
            $executed = mysqli_stmt_execute($stmt);

            if ($executed) {
                $createdUser = true;
                mysqli_stmt_close($stmt);
                break;
            }

            $stmtErrNo = mysqli_stmt_errno($stmt);
            $stmtErr = mysqli_stmt_error($stmt);
            mysqli_stmt_close($stmt);

            // Duplicate key can occur under concurrent registration; retry on generated ID collision.
            if ((int) $stmtErrNo !== 1062) {
                throw new Exception('Error creating user: ' . $stmtErr);
            }
        }

        if (!$createdUser) {
            throw new Exception('Unable to generate a unique user ID. Please try again.');
        }

        // Create linked role profile row in the same transaction.
        createRoleProfile($conn, $role, $user_id, $data, $fullName);

        // Commit transaction
        if ($useTransaction) {
            mysqli_commit($conn);
            mysqli_autocommit($conn, true);
        }

        // Set session
        $_SESSION['user_id'] = $user_id;
        $_SESSION['username'] = $username;
        $_SESSION['email'] = $email;
        $_SESSION['role'] = $role;

        echo json_encode(array(
            'success' => true,
            'message' => 'Account created successfully',
            'user_id' => $user_id,
            'role' => $role
        ));

    } catch (Exception $e) {
        // Rollback transaction on error
        if ($useTransaction) {
            mysqli_rollback($conn);
            mysqli_autocommit($conn, true);
        }
        echo json_encode(array('success' => false, 'message' => $e->getMessage()));
    }

    exit;
}

echo json_encode(array('success' => false, 'message' => 'Invalid request'));
?>
