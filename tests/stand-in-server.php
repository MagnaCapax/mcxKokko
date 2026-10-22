<?php
// Stand-in for the AI credit API, run by tests/run.php with `php -S 127.0.0.1:<port>`.
// The key in the Authorization header picks the scenario, so one server covers every case.

$auth = $_SERVER['HTTP_AUTHORIZATION'] ?? (function_exists('getallheaders') ? (getallheaders()['Authorization'] ?? '') : '');
$scenario = preg_match('/^Bearer vsk-test-([a-z]+)-/', $auth, $m) === 1 ? $m[1] : 'none';
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
header('Content-Type: application/json');

if ($scenario === 'none' || $scenario === 'unauthorized') {
    http_response_code(401);
    echo '{"error":{"message":"invalid key"}}';
    return;
}
if ($scenario === 'broken') {
    http_response_code(500);
    echo '{"error":{"message":"server error"}}';
    return;
}
if ($scenario === 'garbled') {
    header('Content-Type: text/html');
    echo '<!doctype html><title>back shortly</title>';
    return;
}

$balance = array('healthy' => 5000000, 'low' => 90000, 'idle' => 90000)[$scenario] ?? 0;
if ($path === '/v1/balance') {
    echo json_encode(array('balance' => $balance, 'reserved' => 0, 'in_flight' => 0));
    return;
}
if ($path === '/v1/usage') {
    $daily = $scenario === 'idle' ? array() : array(array('day' => '2026-10-20', 'credits' => 30000), array('day' => '2026-10-21', 'credits' => 40000));
    echo json_encode(array('balance' => $balance, 'days' => 7, 'daily' => $daily, 'models' => array(), 'grants' => array()));
    return;
}
http_response_code(404);
echo '{"error":{"message":"no route"}}';
