<?php

$model = 'microsoft/Phi-3-mini-4k-instruct';
$apiUrl = "https://router.huggingface.co/$model";
$apiKey = ''; // replace with your actual token

$headers = [
    "Authorization: Bearer $apiKey",
    "Content-Type: application/json",
];

$input = [
    "inputs" => "Write a short poem about PHP and APIs.",
];

$ch = curl_init($apiUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => $headers,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($input),
    CURLOPT_TIMEOUT => 120,
    CURLOPT_FOLLOWLOCATION => true,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

echo "<pre>";
echo "HTTP Code: $httpCode\n";
echo "cURL Error: " . curl_error($ch) . "\n";
echo "Raw Response:\n";
var_dump($response);
echo "</pre>";

curl_close($ch);

if ($response) {
    $lines = explode("\n", trim($response));
    foreach ($lines as $line) {
        if (strpos($line, 'generated_text') !== false) {
            echo "<h3>Extracted Output:</h3>";
            echo htmlspecialchars($line);
        }
    }
}
