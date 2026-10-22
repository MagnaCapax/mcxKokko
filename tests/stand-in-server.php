<?php
// Stand-in for the AI credit API, run by tests/run.php with `php -S 127.0.0.1:<port>`.
// The key in the Authorization header picks the scenario, so one server covers every case.

$auth = $_SERVER['HTTP_AUTHORIZATION'] ?? (function_exists('getallheaders') ? (getallheaders()['Authorization'] ?? '') : '');
$scenario = preg_match('/^Bearer vsk-test-([a-z]+)-/', $auth, $m) === 1 ? $m[1] : 'none';
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
header('Content-Type: application/json');

// Stand-in for the customer order endpoint: the X-API-Key picks the scenario; every request is counted.
if ($path === '/remote/pmapi-order.php') {
    file_put_contents(sys_get_temp_dir() . '/kokko-stand-in-orders-' . $_SERVER['SERVER_PORT'] . '.log', "1\n", FILE_APPEND);
    $key = $_SERVER['HTTP_X_API_KEY'] ?? '';
    $case = preg_match('/^pm_test-([a-z]+)-x*$/', $key, $k) === 1 && strlen($key) === 46 ? $k[1] : 'none';
    $in = json_decode((string) file_get_contents('php://input'), true);
    $quote = array('product' => 'M10G', 'pid' => $in['pid'] ?? 0, 'billing_cycle' => $in['billingcycle'] ?? '', 'first_payment' => '12.50', 'currency' => 'EUR', 'terms' => 'https://terms.example/tos');
    $out = function (int $code, array $body): void { http_response_code($code); echo json_encode($body); };
    if ($case === 'none' || $case === 'unauthorized') { $out(401, array('error' => 'missing or invalid API key')); return; }
    if (!is_array($in) || ($in['customfields']['Username'] ?? null) !== 'myname') { $out(422, array('error' => 'the order cannot be placed', 'problems' => array('Username is required'))); return; }
    if (empty($in['confirm'])) {
        $clean = !isset($in['accept_tos']) && !isset($in['expect_first_payment']) && !isset($in['expect_currency']);
        $clean ? $out(200, array('quote' => $quote, 'next' => 'send the same request with "confirm": true')) : $out(400, array('error' => 'a quote carries no confirm fields'));
        return;
    }
    if (($in['accept_tos'] ?? null) !== true || ($in['expect_first_payment'] ?? null) !== '12.50' || ($in['expect_currency'] ?? null) !== 'EUR') {
        $out(400, array('error' => 'confirm must carry accept_tos and the quoted price'));
        return;
    }
    if ($case === 'changed') { $out(409, array('error' => 'nothing was ordered: the first payment is now 13.00 EUR, not 12.50', 'quote' => array('first_payment' => '13.00') + $quote)); return; }
    if ($case === 'broken') { $out(500, array('error' => 'temporarily unavailable; no order was placed')); return; }
    $out(201, array('order_id' => 4242, 'invoice_id' => 777, 'pay_url' => 'https://billing.example/viewinvoice.php?id=777', 'quote' => $quote));
    return;
}

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
