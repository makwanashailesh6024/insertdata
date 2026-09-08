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
// CREATE ORDER
// ============================================================
//
// Fixed $10 test payment.
//
// No phone.
// No customer data.
// No empty fields.
// ============================================================

$orderData = array(

    'intent' =>
        'CAPTURE',

    'purchase_units' => array(

        array(

            'amount' => array(

                'currency_code' =>
                    'USD',

                'value' =>
                    '10.00'

            )

        )

    )

);


// ============================================================
// CREATE ORDER REQUEST
// ============================================================

$ch = curl_init();

curl_setopt_array($ch, array(

    CURLOPT_URL =>
        $paypalUrl .
        '/v2/checkout/orders',

    CURLOPT_RETURNTRANSFER =>
        true,

    CURLOPT_POST =>
        true,

    CURLOPT_POSTFIELDS =>
        json_encode($orderData),

    CURLOPT_HTTPHEADER => array(

        'Content-Type: application/json',

        'Accept: application/json',

        'Authorization: Bearer ' .
            $accessToken

    ),

    CURLOPT_TIMEOUT =>
        30

));


$orderResponse =
    curl_exec($ch);


if ($orderResponse === false) {

    $error =
        curl_error($ch);

    curl_close($ch);

    http_response_code(500);

    echo json_encode(array(

        'error' =>
            'PayPal create order cURL error',

        'details' =>
            $error

    ));

    exit;
}


$orderHttpCode =
    curl_getinfo(
        $ch,
        CURLINFO_HTTP_CODE
    );


curl_close($ch);


// ============================================================
// RETURN PAYPAL JSON
// ============================================================

http_response_code(
    $orderHttpCode
);

echo $orderResponse;

exit;
