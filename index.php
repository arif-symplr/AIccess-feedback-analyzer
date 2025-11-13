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
           width: 80%;
           border-collapse: collapse;
           margin: 30px auto;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
            word-wrap: break-word; /* prevent overflow text */
        }
        /* Specific column widths */
        th:nth-child(1),
        td:nth-child(1) {
            width: 20%; /* Ticket ID column */
        }

        th:nth-child(2),
        td:nth-child(2) {
            width: 25%; /* Theme column */
        }

        th:nth-child(3),
        td:nth-child(3) {
            width: 55%; /* Suggestion column */
        }
        
        th {
            background-color: #f4f4f4;
        }
        
        .sentiment-positive {
            text-align: center;       /* centers the text horizontally */
            font-size: 1.2em;         /* optional: makes it stand out */
            font-weight: bold;        /* optional: bold text */
            padding: 10px;     
            background-color: #4CAF50; /* green */
            color: white;
        }
        .sentiment-negative {
            text-align: center;       /* centers the text horizontally */
            font-size: 1.2em;         /* optional: makes it stand out */
            font-weight: bold;        /* optional: bold text */
            padding: 10px;     
            background-color: #f44336; /* red */
            color: white;
        }
        .sentiment-neutral {
            text-align: center;       /* centers the text horizontally */
            font-size: 1.2em;         /* optional: makes it stand out */
            font-weight: bold;        /* optional: bold text */
            padding: 10px;     
            background-color: rgba(97, 145, 236, 1); /* yellow */
            color: white;
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
$apiKey = ''; // get from https://dashboard.cohere.com/api-keys
$url = "https://api.cohere.ai/v2/chat";

$prompt = "<<<PROMPT
        Portal users — clinicians, administrators, and IT staff — often provide feedback via support
        tickets, surveys, and in-app interactions. However, manually analyzing this feedback is time-consuming
        and may miss patterns or sentiments that could improve the user experience.
        Your task is to analyze feedback and generate insights Analyze a small set of
        user feedback (e.g., support tickets or survey responses) Parse the response into structured output: 1. Actual feedback text from the ticket 2.Sentiment: Positive / Neutral / Negative
        3.Theme: Navigation / Form Design / Performance / Visual Design 4 .Suggestion: 'Simplify form layout and add progress indicators'.  
        Give me the entire output in JSON format. Do not give any other explanations.Remove the backticks and 'json' text in the output.
                 
       $feedbackText
         PROMPT";

$payload = [
    "model" => "command-a-03-2025",
    "messages" => [
        [
            "role" => "user",
            "content" => [
                ["type" => "text", "text" => "$prompt"]
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

$responseText = json_decode($response, true);

$responseMessage = $responseText['message']['content'][0]['text'];


$data = json_decode($responseMessage, true);
var_dump($data);
?>



<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Feedback Table</title>
</head>
<body>

<?php
// Group data by sentiment
$groupedData = [];
foreach ($data as $ticketId => $item) {
    $sentiment = $item['Sentiment'];
    $groupedData[$sentiment][$ticketId] = $item;
}
?>

<?php foreach ($groupedData as $sentiment => $tickets): ?>
    <?php
        // Determine sentiment color class
        $sentimentClass = '';

        if ($sentiment == 'Positive') {
            $sentimentClass = 'sentiment-positive';
        } elseif ($sentiment == 'Negative' ) {
            $sentimentClass = 'sentiment-negative';
        } else {
            $sentimentClass = 'sentiment-neutral';
        }
    ?>
    <table>
         <tr>
            <th colspan="3" class='<?php echo $sentimentClass ?>'><?= htmlspecialchars($sentiment) ?></th>
        </tr>
        <tr>
            <th>Ticket ID</th>
            <th>Theme</th>
            <th>Suggestion</th>
        </tr>

        <?php foreach ($tickets as $ticketId => $item): ?>
            <tr>
                <td><?= htmlspecialchars($ticketId) ?></td>
                <td><?= htmlspecialchars($item['Theme']) ?></td>
                <td><?= htmlspecialchars($item['Suggestion']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endforeach; } ?>
</body>
</html>
