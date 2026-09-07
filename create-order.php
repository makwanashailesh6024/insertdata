<?php

header('Content-Type: application/json; charset=utf-8');

// NEVER put these credentials in JavaScript/HTML.
$clientId = 'BAAJlXs7VPXMRzjspBJTaNOJTpSxGjcuGdb0qYRdp98aaD9JNgqJkuGNs0ZXOiGIQUWaRzg0z7oGLWHMW8';
$clientSecret = 'EBoZVPZ0CEvrjyW57qXpmRYx48n0JI1IanM5IaRqKBiGGI3KiYDHr-HZ6B5mBAeTFF69ofj9HAhjXUd4';

function jsonResponse($data, $status = 200)
{
    http_response_code($status);
    echo json_encode($data);
    exit;
}

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse([
        'error' => 'POST request required'
    ], 405);
}

// Check cURL
if (!function_exists('curl_init')) {
    jsonResponse([
        'error' => 'PHP cURL extension is not enabled'
    ], 500);
}

/*
 * ---------------------------------------------------------
 * 1. Get PayPal OAuth access token
 * ---------------------------------------------------------
 */

$ch = curl_init();

curl_setopt_array($ch, [
    CURLOPT_URL => 'https://api-m.sandbox.paypal.com/v1/oauth2/token',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_USERPWD => $clientId . ':' . $clientSecret,
    CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
    CURLOPT_HTTPHEADER => [
        'Accept: application/json',
        'Accept-Language: en_US',
        'Content-Type: application/x-www-form-urlencoded'
    ],
    CURLOPT_TIMEOUT => 30
]);

$tokenResponse = curl_exec($ch);

if ($tokenResponse === false) {
    $error = curl_error($ch);
    curl_close($ch);

    jsonResponse([
        'error' => 'PayPal OAuth cURL error',
        'details' => $error
    ], 500);
}

$tokenHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

$tokenData = json_decode($tokenResponse, true);

if (!is_array($tokenData)) {
    jsonResponse([
        'error' => 'PayPal OAuth returned invalid JSON',
        'http_code' => $tokenHttpCode,
        'paypal_response' => $tokenResponse
    ], 500);
}

if (empty($tokenData['access_token'])) {
    jsonResponse([
        'error' => 'Could not obtain PayPal access token',
        'http_code' => $tokenHttpCode,
        'paypal_response' => $tokenData
    ], 401);
}

$accessToken = $tokenData['access_token'];


/*
 * ---------------------------------------------------------
 * 2. Read request from browser
 * ---------------------------------------------------------
 */

$rawInput = file_get_contents('php://input');

$input = json_decode($rawInput, true);

if (!is_array($input)) {
    jsonResponse([
        'error' => 'Invalid JSON received from browser',
        'received' => $rawInput
    ], 400);
}


/*
 * ---------------------------------------------------------
 * 3. Amount
 * ---------------------------------------------------------
 *
 * In a real shop, calculate the amount on the server
 * instead of trusting the browser.
 */

$amount = '10.00';

if (isset($input['amount'])) {
    $amount = number_format((float)$input['amount'], 2, '.', '');
}

if ((float)$amount <= 0) {
    jsonResponse([
        'error' => 'Invalid amount'
    ], 400);
}


/*
 * ---------------------------------------------------------
 * 4. Create PayPal order
 * ---------------------------------------------------------
 */

$orderData = [
    'intent' => 'CAPTURE',
    'purchase_units' => [
        [
            'amount' => [
                'currency_code' => 'USD',
                'value' => $amount
            ]
        ]
    ]
];

$ch = curl_init();

curl_setopt_array($ch, [
    CURLOPT_URL => 'https://api-m.sandbox.paypal.com/v2/checkout/orders',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($orderData),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Bearer ' . $accessToken
    ],
    CURLOPT_TIMEOUT => 30
]);

$orderResponse = curl_exec($ch);

if ($orderResponse === false) {
    $error = curl_error($ch);
    curl_close($ch);

    jsonResponse([
        'error' => 'PayPal create-order cURL error',
        'details' => $error
    ], 500);
}

$orderHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

$orderDataResponse = json_decode($orderResponse, true);

if (!is_array($orderDataResponse)) {
    jsonResponse([
        'error' => 'PayPal returned invalid JSON',
        'http_code' => $orderHttpCode,
        'paypal_response' => $orderResponse
    ], 500);
}


/*
 * ---------------------------------------------------------
 * 5. Return PayPal response to browser
 * ---------------------------------------------------------
 */

http_response_code($orderHttpCode);

echo json_encode($orderDataResponse);