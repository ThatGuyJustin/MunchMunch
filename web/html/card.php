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

if(!isset($_GET["id"])){
    echo("Recipe not found.");
    exit();
} else {
    $recipe_id = $_GET["id"];
}

$recipe = api_request_with_token("api/recipes/$recipe_id");
if (!isset($recipe['data'])) {
    echo("Recipe not found.");
    exit();
}
$recipe = $recipe["data"];

$who_did_it = api_request_with_token("api/users/" . $recipe["user"]);
$all_tags = api_request_with_token("api/tags");

$needed_tags = array();
foreach($all_tags['data'] as &$tag) {
    if(in_array($tag["id"], $recipe["tags"])) {
        array_push($needed_tags, $tag["emoji"] . $tag["label"]);
    }
}

$ingredients = array();
foreach($recipe["ingredients"] as $key => $value) {
    array_push($ingredients, "<br>• " . htmlspecialchars($key) . ": " . htmlspecialchars($value));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($recipe["title"]); ?></title>
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
            border-radius: 20px;
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
            border-radius: 12px;
        }
        .buttons button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Title and Author Section -->
    <div class="title-section">
        <h1><?php echo htmlspecialchars($recipe["title"]); ?></h1>
        <span class="author">By <?php echo htmlspecialchars($who_did_it["data"]["name"]); ?> (<?php echo htmlspecialchars($who_did_it["data"]["id"]); ?>)</span>
    </div>

    <!-- Main Content Section -->
    <div class="main-content">
        <!-- Left Panel with Recipe Details -->
        <div class="left-panel">
            <div class="recipe-details">Tags: <?php echo implode(", ", $needed_tags); ?></div>
            <div class="recipe-details">Time to Cook: <?php echo htmlspecialchars($recipe["time_to_cook"] / 60); ?> Minutes</div>
            <div class="recipe-details">Skill Level: <?php echo htmlspecialchars($recipe["skill_level"]); ?></div>
            <div class="recipe-details">Steps: <br>• <?php echo implode("<br>• ", $recipe["steps"]); ?></div>
            <div class="recipe-details">Ingredients: <?php echo(implode($ingredients))?> </div>
            <div class="buttons">
                <a href="recipeinfo.php?id=<?php echo htmlspecialchars($recipe_id); ?>">
                    <button>Cook!</button>
                </a>
                <button id="favorite_button">Save for later</button>
            </div>
        </div>
    </div>

    <!-- Review Section -->
    <div class="review-section">
        <h2>Review</h2>
        <textarea placeholder="Write your review here..."></textarea>
    </div>
</div>

</body>
</html>
