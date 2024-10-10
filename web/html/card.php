<?php
require_once 'util.php'; // Include utility functions

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

start_session();

if (!is_user_logged_in()) {
    header('Location: login.php');
    exit();
}

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve user inputs
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $tags = isset($_POST['tags']) ? explode(",", $_POST['tags']) : [];
    $steps = isset($_POST['steps']) ? explode("\n", trim($_POST['steps'])) : [];
    $time_to_cook = intval($_POST['time_to_cook'] ?? 0);
    $time_to_prepare = intval($_POST['time_to_prepare'] ?? 0);
    $skill_level = intval($_POST['skill_level'] ?? 1);
    $user_id = $_SESSION['user_id'];

    // Prepare data for the API request
    $data = [
        'user' => $user_id,
        'title' => $title,
        'description' => $description,
        'tags' => $tags,
        'steps' => $steps,
        'time_to_cook' => $time_to_cook,
        'time_to_prepare' => $time_to_prepare,
        'skill_level' => $skill_level
    ];

    // API URL
    $api_url = "http://backend:5000/api/recipes"; // Correct API endpoint

    // Initialize cURL
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Follow redirects

    // Execute the request
    $response = curl_exec($ch);


    curl_close($ch);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 20px; /* Rounder edges for the container */
        }
        .title-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .title-section h1 {
            font-size: 32px;
            margin: 0;
        }
        .title-section .author {
            font-size: 18px;
            color: gray;
        }
        .main-content {
            display: flex;
            margin-top: 20px;
        }
        .left-panel {
            width: 50%;
        }
        .right-panel {
            width: 50%;
            text-align: right;
        }
        .right-panel img {
            width: 100%;
            border-radius: 20px; /* Rounder edges for the image */
        }
        .recipe-details {
            font-size: 18px;
            margin-bottom: 10px;
        }
        .buttons {
            margin-top: 20px;
        }
        .buttons button {
            background-color: #266fff;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            margin-right: 10px;
            border-radius: 12px; /* Rounder edges for the buttons */
        }
        .buttons button:hover {
            background-color: #218838;
        }
        .review-section {
            margin-top: 40px;
        }
        .review-section h2 {
            font-size: 24px;
            margin-bottom: 10px;
        }
        .review-section textarea {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 12px; /* Rounder edges for the review textarea */
            height: 100px;
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Title and Author Section -->
    <div class="title-section">
        <h1><?php echo htmlspecialchars($title); ?></h1>
        <span class="author">By <?php echo htmlspecialchars($_SESSION['username']); ?> (<?php echo htmlspecialchars($user_id); ?>)</span>
    </div>

    <!-- Main Content Section -->
    <div class="main-content">
        <!-- Left Panel with Recipe Details -->
        <div class="left-panel">
            <div class="recipe-details">Tags: <?php echo implode(", ", $tags); ?></div>
            <div class="recipe-details">Time to Cook: <?php echo htmlspecialchars($time_to_cook / 60); ?> Minutes</div>
            <div class="recipe-details">Skill Level: <?php echo htmlspecialchars($skill_level); ?></div>
            <!-- If you have additional menu labels, include them dynamically as needed -->
            <div class="recipe-details">Menu Label 1</div>
            <div class="recipe-details">Menu Label 2</div>
            <div class="buttons">
                <button>Cook!</button>
                <button>Save for later</button>
            </div>
        </div>

        <!-- Right Panel with Recipe Image -->
        <!--<div class="right-panel">
            <img src="html/pictures/recipe/macandcheese.jpg" alt="<?php echo htmlspecialchars($title); ?>">
        </div>
    </div>-->

    <!-- Review Section -->
    <div class="review-section">
        <h2>Review</h2>
        <textarea placeholder="Write your review here..."></textarea>
    </div>
</div>

</body>
</html>
