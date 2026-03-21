<?php
header('Content-Type: application/json');

$checks = array(
    'php_version' => phpversion(),
    'sapi' => php_sapi_name(),
    'mysqli_available' => function_exists('mysqli_connect'),
    'json_available' => function_exists('json_encode'),
    'session_available' => function_exists('session_start'),
    'password_hash_available' => function_exists('password_hash'),
    'password_verify_available' => function_exists('password_verify')
);

try {
    require_once(dirname(__FILE__) . '/../config/db.php');
    $checks['db_bootstrap'] = 'ok';
    $checks['db_connected'] = isset($conn) && $conn ? true : false;
} catch (Exception $e) {
    $checks['db_bootstrap'] = 'exception';
    $checks['exception'] = $e->getMessage();
}

echo json_encode(array('success' => true, 'checks' => $checks));
