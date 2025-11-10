<?php
$apiKey = ''; // get from https://dashboard.cohere.com/api-keys
$url = "https://api.cohere.ai/v2/chat";

$payload = [
    "model" => "command-a-03-2025",
    "messages" => [
        [
            "role" => "user",
            "content" => [
                ["type" => "text", "text" => "
                    Portal users — clinicians, administrators, and IT staff — often provide feedback via support 
                    tickets, surveys, and in-app interactions. However, manually analyzing this feedback is time-consuming 
                    and may miss patterns or sentiments that could improve the user experience. 
                    Your task is to analyze feedback and generate insights Analyze a small set of 
                    user feedback (e.g., support tickets or survey responses) and generate: 1. Sentiment analysis, 
                    2. Categorization by UX themes, 3. Actionable improvement suggestions. Given below is a sample feedback:
                    
                    Columns :Ticket ID ,Feedback Text
                     001, The access request form is confusing and takes too long to fill. 
                     002, I couldn’t find the audit logs easily. 
                     003, Approvals are fast, but UI feels outdated. 
                     
                     
                "]
            ]
        ]
    ]
];

$headers = [
    "Authorization: Bearer $apiKey",
    "Content-Type: application/json",
];

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => $headers,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<pre>HTTP Code: $httpCode\n</pre>";
//echo "<pre>Raw Response:\n$response\n</pre>";

$responseText = json_decode($response, true);
var_dump($responseText['message']['content'][0]['text']);


$data = json_decode($response, true);
if (isset($data["text"])) {
    echo "<h3>AI Reply:</h3>";
    echo htmlspecialchars($data["text"]);
}
