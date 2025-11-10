<?php
require 'vendor/autoload.php';

use Dotenv\Dotenv;
use OpenAI;

// Load .env (optional)
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

$apiKey = $_ENV['OPENAI_API_KEY'] ?? getenv('OPENAI_API_KEY');

if (!$apiKey) {
    die("❌ Missing OPENAI_API_KEY. Set it in .env or environment variables.\n");
}

$client = OpenAI::client($apiKey);

$response = $client->chat()->create([
    'model' => 'gpt-4o-mini',
    'messages' => [
        ['role' => 'system', 'content' => 'You are a PHP coding assistant.'],
        ['role' => 'user', 'content' => 'Write a short poem about PHP developers.'],
    ],
]);

echo "<h2>OpenAI API Response:</h2>";
echo "<pre>" . htmlspecialchars($response->choices[0]->message->content) . "</pre>";
