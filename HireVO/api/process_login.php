<?php
ob_start();

function hirevo_login_shutdown_handler() {
    $lastError = error_get_last();
    if (!$lastError) {
        return;
    }

    $fatalTypes = array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR);
    if (!in_array($lastError['type'], $fatalTypes)) {
        return;
    }

    error_log('HireVO login fatal: ' . $lastError['message'] . ' in ' . $lastError['file'] . ':' . $lastError['line']);

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
        'message' => 'Server fatal error in login API.',
        'fatal' => $lastError['message'],
        'file' => basename($lastError['file']),
        'line' => $lastError['line']
    ));
}

register_shutdown_function('hirevo_login_shutdown_handler');

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

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($data['action']) && $data['action'] === 'login') {
    $email = sanitize(isset($data['email']) ? $data['email'] : '', $conn);
    $password = isset($data['password']) ? $data['password'] : '';

    // Validation
    if (empty($email) || empty($password)) {
        echo json_encode(array('success' => false, 'message' => 'Email and password are required'));
        exit;
    }

    if (!validateEmail($email)) {
        echo json_encode(array('success' => false, 'message' => 'Invalid email format'));
        exit;
    }

    // Query user (prepared statement) using bind_result for wider host compatibility
    $stmt = mysqli_prepare($conn, "SELECT user_id, username, email, password, role, status FROM users WHERE email = ? LIMIT 1");
    if (!$stmt) {
        echo json_encode(array('success' => false, 'message' => 'Database error'));
        exit;
    }

    mysqli_stmt_bind_param($stmt, 's', $email);
    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        echo json_encode(array('success' => false, 'message' => 'Database error'));
        exit;
    }

    mysqli_stmt_bind_result($stmt, $userId, $username, $userEmail, $passwordHash, $role, $status);
    if (!mysqli_stmt_fetch($stmt)) {
        mysqli_stmt_close($stmt);
        echo json_encode(array('success' => false, 'message' => 'Invalid email or password'));
        exit;
    }
    mysqli_stmt_close($stmt);

    $user = array(
        'user_id' => $userId,
        'username' => $username,
        'email' => $userEmail,
        'password' => $passwordHash,
        'role' => $role,
        'status' => $status
    );

    // Check account status
    if ($user['status'] !== 'active') {
        echo json_encode(array('success' => false, 'message' => 'Your account is ' . $user['status']));
        exit;
    }

    // Verify password
    // Try bcrypt first (new registrations), then fall back to SHA-256 (legacy seed data)
    $passwordValid = false;

    if (verifyPassword($password, $user['password'])) {
        // Bcrypt match (new users)
        $passwordValid = true;
    } elseif (hash('sha256', $password) === $user['password']) {
        // SHA-256 match (legacy seed users)
        $passwordValid = true;

        // Optionally upgrade the legacy hash to bcrypt for future logins
        if (function_exists('password_hash')) {
            $newHash = password_hash($password, PASSWORD_BCRYPT);
            $updateStmt = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE user_id = ?");
            if ($updateStmt) {
                mysqli_stmt_bind_param($updateStmt, 'ss', $newHash, $user['user_id']);
                mysqli_stmt_execute($updateStmt);
                mysqli_stmt_close($updateStmt);
            }
        }
    }

    if (!$passwordValid) {
        echo json_encode(array('success' => false, 'message' => 'Invalid email or password'));
        exit;
    }

    // Set session
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];

    // Determine redirect based on role
    $redirect = '';
    switch($user['role']) {
        case 'admin':
            $redirect = 'admin/dashboard.php';
            break;
        case 'employer':
            $redirect = 'employer/dashboard.php';
            break;
        case 'job_seeker':
            $redirect = 'jobseeker/dashboard.php';
            break;
    }

    echo json_encode(array(
        'success' => true,
        'message' => 'Login successful',
        'redirect' => $redirect
    ));
    exit;
}

echo json_encode(array('success' => false, 'message' => 'Invalid request'));
?>
