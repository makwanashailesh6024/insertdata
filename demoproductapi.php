<?php

header('Content-Type: application/json');

$url = 'https://dummyjson.com/products';

$ch = curl_init();

curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTPHEADER => [
        'Accept: application/json'
    ]
]);

$response = curl_exec($ch);

if ($response === false) {
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => curl_error($ch)
    ]);

    curl_close($ch);
    exit;
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

if ($httpCode >= 400) {
    http_response_code($httpCode);

    echo json_encode([
        'success' => false,
        'message' => 'External API error'
    ]);

    exit;
}

// Convert API response to PHP array
$data = json_decode($response, true);
print_r($data);

