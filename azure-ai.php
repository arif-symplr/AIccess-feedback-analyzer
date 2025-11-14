<?php
require 'vendor/autoload.php';
use Dotenv\Dotenv;

// Load .env (optional)
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>AIccess Feedback Analyzer</title>
    <style>
        h1 {
            margin-top: 40px;
            font-size: 28px;
            color: #333;
            text-align: center;
        }

        .error-message {
            color: red;
            font-size: 16px;
            margin-top: 10px;
            text-align: center;
            font-weight: bold;
        }

        /* Form Container */
        .form-container {
            width: 80%;
            margin: 30px auto;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
        }

        input[type="file"] {
            padding: 8px 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            background-color: #fff;
            font-size: 14px;
            cursor: pointer;
        }

        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 18px;
            font-size: 14px;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.2s ease-in-out;
        }

        button:hover {
            background-color: #45a049;
        }

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
<h1>AIccess Feedback Analyzer</h1>
<form action="" method="post" enctype="multipart/form-data">
    <div class="form-container">
        <input type="file" name="csv_file" accept=".csv" >
        <button type="submit" name="upload">Analyze CSV</button>
    </div>
</form>
<?php
$feedbackText = '';
if (isset($_POST['upload'])) {
    if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] == 0) {
        $fileTmpPath = $_FILES['csv_file']['tmp_name'];

        // Read the CSV file
        $csvData = file($fileTmpPath);
        echo "<pre>";
        foreach ($csvData as $line) {
            $feedbackText .= trim($line) . "\n";
        }

        echo "</pre>";
    } else {
        echo "<p class='error-message' style='color:red;'>Error: Please select a valid CSV file.</p>";
    }
}
?>


<?php
if (isset($_POST['upload']) && isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] == 0) {
$apiKey = $_ENV['AZURE_API_KEY'] ?? getenv('AZURE_API_KEY');
$endpoint = "https://aarif-mhygl3n5-eastus2.cognitiveservices.azure.com/openai/deployments/gpt-5-chat/chat/completions?api-version=2025-01-01-preview";

$prompt = "<<<PROMPT
        Portal users — clinicians, administrators, and IT staff — often provide feedback via support
        tickets, surveys, and in-app interactions. However, manually analyzing this feedback is time-consuming
        and may miss patterns or sentiments that could improve the user experience.
        Your task is to analyze feedback and generate insights Analyze a small set of
        user feedback (e.g., support tickets or survey responses) Parse the response into structured output: 1.Sentiment: Positive / Neutral / Negative
        2.Theme: Navigation / Form Design / Performance / Visual Design 3.Suggestion: 'Simplify form layout and add progress indicators'. 
        Give me the entire output in JSON format. Do not give any other explanations.Remove the backticks and 'json' text in the output.
                 
       $feedbackText
         PROMPT";

$data = [
        'messages' => [
                ['role' => 'system', 'content' => 'You are a helpful assistant.'],
                ['role' => 'user', 'content' => $prompt]
        ],
        'temperature' => 0.7,
//        'max_tokens' => 105
];

$options = [
        'http' => [
                'header' => "Content-Type: application/json\r\n" .
                        "api-key: $apiKey\r\n",
                'method' => 'POST',
                'content' => json_encode($data)
        ]
];

$context = stream_context_create($options);
$result = file_get_contents($endpoint, false, $context);
$response = '';
//var_dump($result);die;

if ($result === FALSE) {
    echo "Error calling API.";
} else {
    $response = json_decode($result, true);
//    var_dump(json_decode($response['choices'][0]['message']['content']), true);die;
    $responseMessage = $response['choices'][0]['message']['content'];
//    var_dump($responseMessage);
//    var_dump(json_decode($responseMessage, true));

    $data = json_decode($responseMessage, true)['feedback_analysis'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Feedback Table</title>
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
    <?php endforeach; }?>
</table>

</body>
</html>
