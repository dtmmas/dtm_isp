<?php

require __DIR__ . '/vendor/autoload.php';

$cfg = whatsappConfig();
$body = [
    'messaging_product' => 'whatsapp',
    'to' => '50233024105',
    'type' => 'text',
    'text' => [
        'preview_url' => false,
        'body' => 'Diagnostico DTM ' . date('Y-m-d H:i:s'),
    ],
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://graph.facebook.com/v15.0/' . $cfg->number . '/messages');
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: Application/json',
    'Authorization: Bearer ' . $cfg->token,
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo json_encode([
    'http_code' => $http_code,
    'response' => json_decode($response, true),
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
