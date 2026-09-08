<?php

header('Content-Type: application/json; charset=utf-8');


// ============================================================
// PAYPAL SANDBOX CREDENTIALS
// ============================================================

$clientId = 'BAAJlXs7VPXMRzjspBJTaNOJTpSxGjcuGdb0qYRdp98aaD9JNgqJkuGNs0ZXOiGIQUWaRzg0z7oGLWHMW8';
$clientSecret = 'EBoZVPZ0CEvrjyW57qXpmRYx48n0JI1IanM5IaRqKBiGGI3KiYDHr-HZ6B5mBAeTFF69ofj9HAhjXUd4';


// ============================================================
// ONLY POST
// ============================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    http_response_code(405);

    echo json_encode(array(
        'error' => 'POST request required'
    ));

    exit;
}


// ============================================================
// CHECK CURL
// ============================================================

if (!function_exists('curl_init')) {

    http_response_code(500);

    echo json_encode(array(
        'error' => 'PHP cURL extension is not enabled'
    ));

    exit;
}


// ============================================================
// READ REQUEST
// ============================================================

$rawInput =
    file_get_contents(
        'php://input'
    );


$input =
    json_decode(
        $rawInput,
        true
    );


// ============================================================
// CHECK ORDER ID
// ============================================================

if (
    !is_array($input) ||
    !isset($input['orderID']) ||
    empty($input['orderID'])
) {

    http_response_code(400);

    echo json_encode(array(

        'error' =>
            'PayPal orderID is required'

    ));

    exit;
}


$orderId =
    $input['orderID'];


// ============================================================
// PAYPAL SANDBOX URL
// ============================================================

$paypalUrl =
    'https://api-m.sandbox.paypal.com';


// ============================================================
// GET ACCESS TOKEN
// ============================================================

$ch = curl_init();

curl_setopt_array($ch, array(

    CURLOPT_URL =>
        $paypalUrl .
        '/v1/oauth2/token',

    CURLOPT_RETURNTRANSFER =>
        true,

    CURLOPT_POST =>
        true,

    CURLOPT_USERPWD =>
        $clientId .
        ':' .
        $clientSecret,

    CURLOPT_POSTFIELDS =>
        'grant_type=client_credentials',

    CURLOPT_HTTPHEADER => array(

        'Accept: application/json',

        'Accept-Language: en_US',

        'Content-Type: application/x-www-form-urlencoded'

    ),

    CURLOPT_TIMEOUT =>
        30

));


$tokenResponse =
    curl_exec($ch);


if ($tokenResponse === false) {

    $error =
        curl_error($ch);

    curl_close($ch);

    http_response_code(500);

    echo json_encode(array(

        'error' =>
            'PayPal OAuth cURL error',

        'details' =>
            $error

    ));

    exit;
}


$tokenHttpCode =
    curl_getinfo(
        $ch,
        CURLINFO_HTTP_CODE
    );


curl_close($ch);


// ============================================================
// DECODE TOKEN
// ============================================================

$tokenData =
    json_decode(
        $tokenResponse,
        true
    );


if (!is_array($tokenData)) {

    http_response_code(500);

    echo json_encode(array(

        'error' =>
            'Invalid PayPal OAuth response',

        'http_code' =>
            $tokenHttpCode,

        'response' =>
            $tokenResponse

    ));

    exit;
}


// ============================================================
// CHECK TOKEN
// ============================================================

if (
    !isset(
        $tokenData['access_token']
    )
) {

    http_response_code(401);

    echo json_encode(array(

        'error' =>
            'Could not obtain PayPal access token',

        'http_code' =>
            $tokenHttpCode,

        'paypal_response' =>
            $tokenData

    ));

    exit;
}


$accessToken =
    $tokenData['access_token'];


// ============================================================
// CAPTURE URL
// ============================================================

$captureUrl =
    $paypalUrl .
    '/v2/checkout/orders/' .
    rawurlencode($orderId) .
    '/capture';


// ============================================================
// CAPTURE PAYMENT
// ============================================================

$ch = curl_init();

curl_setopt_array($ch, array(

    CURLOPT_URL =>
        $captureUrl,

    CURLOPT_RETURNTRANSFER =>
        true,

    CURLOPT_POST =>
        true,

    // Empty JSON object required for capture
    CURLOPT_POSTFIELDS =>
        '{}',

    CURLOPT_HTTPHEADER => array(

        'Content-Type: application/json',

        'Accept: application/json',

        'Authorization: Bearer ' .
            $accessToken

    ),

    CURLOPT_TIMEOUT =>
        30

));


$captureResponse =
    curl_exec($ch);


if ($captureResponse === false) {

    $error =
        curl_error($ch);

    curl_close($ch);

    http_response_code(500);

    echo json_encode(array(

        'error' =>
            'PayPal capture cURL error',

        'details' =>
            $error

    ));

    exit;
}


$captureHttpCode =
    curl_getinfo(
        $ch,
        CURLINFO_HTTP_CODE
    );


curl_close($ch);


// ============================================================
// RETURN ONLY PAYPAL JSON
// ============================================================

http_response_code(
    $captureHttpCode
);

echo $captureResponse;

exit;
