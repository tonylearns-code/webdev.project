<?php
// Database configuration
// IMPORTANT on ByetHost: set these to your real MySQL details from the hosting panel.
// Host usually looks like sqlXXX.byethost.com, not localhost.

function env_or_default($key, $defaultValue) {
    $value = getenv($key);
    if ($value === false || $value === '') {
        return trim($defaultValue);
    }
    return trim($value);
}

define('DB_HOST', env_or_default('DB_HOST', 'sql204.byethost7.com'));
define('DB_USER', env_or_default('DB_USER', 'b7_41428890'));
define('DB_PASS', env_or_default('DB_PASS', '1234567890'));
define('DB_NAME', env_or_default('DB_NAME', 'b7_41428890_HireVoDB'));
define('DB_PORT', (int) env_or_default('DB_PORT', '3306'));

if (!function_exists('mysqli_connect')) {
    error_log('HireVO DB bootstrap error: mysqli extension is not enabled.');
    if (!headers_sent()) {
        if (function_exists('http_response_code')) {
            http_response_code(500);
        } else {
            header('HTTP/1.1 500 Internal Server Error');
        }
        header('Content-Type: application/json');
    }
    die(json_encode(array('success' => false, 'message' => 'Server configuration error: mysqli extension is disabled.')));
}

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

if (!$conn) {
    error_log('HireVO DB connection failed: ' . mysqli_connect_error());
    if (!headers_sent()) {
        header('Content-Type: application/json');
    }
    die(json_encode(array('success' => false, 'message' => 'Database connection failed. Check DB_HOST, DB_USER, DB_PASS, DB_NAME.')));
}

if (function_exists('mysqli_set_charset')) {
    mysqli_set_charset($conn, 'utf8mb4');
} else {
    mysqli_query($conn, "SET NAMES utf8mb4");
}

function sanitize($input, $conn) {
    return mysqli_real_escape_string($conn, trim($input));
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function hashPassword($password) {
    if (function_exists('password_hash')) {
        return password_hash($password, PASSWORD_BCRYPT);
    }
    // Legacy fallback for old PHP versions on shared hosts.
    return hash('sha256', $password);
}

function verifyPassword($password, $hash) {
    if (function_exists('password_verify')) {
        return password_verify($password, $hash);
    }
    return hash('sha256', $password) === $hash;
}
