<?php

$token = "ExponentPushToken[8jPurlHqIgkrzbXmXLHrRU]";

$ch = curl_init();

curl_setopt_array($ch, [
    CURLOPT_URL => "https://exp.host/--/api/v2/push/send",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
    ],
    CURLOPT_POSTFIELDS => json_encode([
        "to" => $token,
        "title" => "TEST",
        "body" => "Halo dari Laravel",
        "sound" => "default",
        "priority" => "high",
        "channelId" => "default"
    ]),
]);

$response = curl_exec($ch);

echo $response;

curl_close($ch);