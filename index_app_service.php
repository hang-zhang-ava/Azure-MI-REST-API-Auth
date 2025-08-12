<?php
require 'vendor/autoload.php';
use GuzzleHttp\Client;
header('Content-Type: text/html');

$endpoint = getenv("IDENTITY_ENDPOINT");
$header = getenv("IDENTITY_HEADER");

$resource = "https://cognitiveservices.azure.com/";
$apiVersion = "2019-08-01";
if (!$endpoint || !$header) {
    echo "<p style='color:red;'>Missing IDENTITY_ENDPOINT or IDENTITY_HEADER environment variables. Are you running in Azure Environment?</p>";
    exit;
}
$client = new Client([
    'timeout' => 10.0,
]);
try {
    $response = $client->request('GET', $endpoint, [
        'headers' => [
            'X-IDENTITY-HEADER' => $header
        ],
        'query' => [
            'resource' => $resource,
            'api-version' => $apiVersion
        ]
    ]);
    $body = json_decode($response->getBody(), true);
    echo "<h2>Managed Identity Access Token</h2>";
    echo "<pre>" . htmlspecialchars(json_encode($body, JSON_PRETTY_PRINT)) . "</pre>";
} catch (Exception $e) {
    echo "<p style='color:red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p style='color:red;'>IDENTITY_ENDPOINT: " . htmlspecialchars($endpoint) . "</p>";
    echo "<p style='color:red;'>IDENTITY_HEADER: " . htmlspecialchars($header) . "</p>";
}
$accessToken = $body['access_token'] ?? '';
if (!$accessToken) {
    exit;
}
$modelName = "<YOUR_MODEL_NAME>";
$apiUrl = "<YOUR_API_URL>";
$message = "認証成功";
$exceptionMessage = "";
$systemMessage = "You are a professional translation expert. Your sole task is to translate messages between English and Japanese.\n"
. "- If the input is in English, translate it into Japanese.\n"
. "- If the input is in Japanese, translate it into English.\n"
. "- If the input contains both English and Japanese, detect which language is dominant (based on word count, ratio, or overall usage), and translate the entire message into that dominant language.\n"
. "- Do not provide explanations, summaries, or any other output.\n"
. "- You must return in a json format with the following structure: {\"language\": \"en or ja based on the translated message\", \"message\": \"translated message\"}.\n"
. "- Do not skip translation or respond in the same language as the input.";

$data = [
    "messages" => [
        [
            "role" => "system",
            "content" => $systemMessage,
        ],
        [
            "role" => "user",
            "content" => $message
        ],
    ],
    "max_tokens" => 800,
    "temperature" => 1,
    "top_p" => 1,
    "frequency_penalty" => 0,
    "presence_penalty" => 0,
    "model" => $modelName
];

$options = [
    'headers' => [
        'Content-Type' => 'application/json',
        'Authorization' => "Bearer $accessToken",
    ],
    'body' => json_encode($data),
];

try{
    $response = $client->post($apiUrl, $options);
    $statusCode = $response?->getStatusCode();
    $responseBody = json_decode($response?->getBody(), true);
    echo "<h2>Response from OpenAI API</h2>";
    echo "<pre>" . htmlspecialchars(json_encode($responseBody, JSON_PRETTY_PRINT)) . "</pre>";
    
}catch(\Exception $e){
    $statusCode = $e?->getCode();
    $exceptionMessage = $e?->getMessage();
    echo "<p style='color:red;'>statusCode: " . htmlspecialchars($statusCode) . "</p>";
    echo "<p style='color:red;'>exceptionMessage: " . htmlspecialchars($exceptionMessage) . "</p>";
    echo "<p style='color:red;'>accessToken: " . htmlspecialchars($accessToken) . "</p>";
    echo "<p style='color:red;'>IDENTITY_ENDPOINT: " . htmlspecialchars($endpoint) . "</p>";
    echo "<p style='color:red;'>IDENTITY_HEADER: " . htmlspecialchars($header) . "</p>";
}
?>
