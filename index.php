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
                    user feedback (e.g., support tickets or survey responses) Parse the response into structured output: 1.Sentiment: Positive / Neutral / Negative
                    2.Theme: Navigation / Form Design / Performance / Visual Design 3.Suggestion: 'Simplify form layout and add progress indicators'. 
                    Give me the entire output in JSON format. Do not give any other explanations.Remove the backticks and 'json' text in the output.
                     
                    
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

//echo "<pre>HTTP Code: $httpCode\n</pre>";
//echo "<pre>Raw Response:\n$response\n</pre>";

$responseText = json_decode($response, true);
//var_dump($responseText['message']['content'][0]['text']);

echo "<pre>" . $responseText['message']['content'][0]['text'] . "</pre>";

$responseMessage = $responseText['message']['content'][0]['text'];

//var_dump($responseMessage);
$data = json_decode($responseMessage, true);

//echo "
//    <table>
//    <tr><th>Ticket ID</th><th>Sentiment</th><th>Theme</th><th>Suggestion</th></tr>
//";
//
//foreach ($data as $ticketID => $feedback) {
//    echo "
//        <tr><td>$ticketID</td>
//        <td>" . $feedback['Sentiment'] . "</td>
//        <td>" . $feedback['Theme'] . "</td>
//        <td>" . $feedback['Suggestion'] . "</td></tr>"
//    ;
//}
//
//echo "</table>";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Feedback Table</title>
    <style>
        table {
            border-collapse: collapse;
            width: 80%;
            margin: 20px auto;
            font-family: Arial, sans-serif;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px 12px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:nth-child(even) {
            background-color: #fafafa;
        }
    </style>
</head>
<body>

<table>
    <tr>
        <th>Ticket ID</th>
        <th>Sentiment</th>
        <th>Theme</th>
        <th>Suggestion</th>
    </tr>

    <?php foreach ($data as $ticketId => $item): ?>
        <tr>
            <td><?= htmlspecialchars($ticketId) ?></td>
            <td><?= htmlspecialchars($item['Sentiment']) ?></td>
            <td><?= htmlspecialchars($item['Theme']) ?></td>
            <td><?= htmlspecialchars($item['Suggestion']) ?></td>
        </tr>
    <?php endforeach; ?>
</table>

</body>
</html>
