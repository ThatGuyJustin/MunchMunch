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

if(!$_GET["id"]){
    echo("Recipe not found.");
}else{
    $recipe_id = $_GET["id"];
}

$recipe = api_request_with_token("api/recipes/$recipe_id");

$recipe = $recipe["data"];

$who_did_it = api_request_with_token("api/users/" . $recipe["user"]);

$all_tags = api_request_with_token("api/tags");

$needed_tags = array();

foreach($all_tags as &$tag){
    if(in_array($tag["id"], $recipe["tags"])){
        array_push($needed_tags, $tag["emoji"] . $tag["label"]);
    }
}


$ingredients = array();


foreach($recipe["ingredients"] as $key => $value){
    array_push($ingredients, "<br>• " . $key . ": " . $value);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <script>
        favorite_button.on_click()
        {
            document.getElementbyId('favorite_button').innerHTML = "Saved!";
            document.getElementById('favorite_button').style.background='#6beb34';
            <?php 
                api_request_with_token("api/users/favorites/$recipe_id", "PUT");
            ?>
        }
    </script>
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
            <!-- If you have additional menu labels, include them dynamically as needed -->
            <div class="recipe-details">Steps: <br>• <?php echo implode("<br>• ", $recipe["steps"]); ?></div>
            <div class="recipe-details">Ingredients: <?php echo(implode($ingredients))?> </div>
            <div class="buttons">
                <button>Cook!</button>
                <button id="favorite_button">Save for later</button>
            </div>
        </div>

        <!-- Right Panel with Recipe Image -->
        <!--<div class="right-panel">
            <img src="html/pictures/recipe/macandcheese.jpg" alt="<?php echo htmlspecialchars($recipe["title"]); ?>">
        </div>  -->
    </div>

    <!-- Review Section -->
    <div class="review-section">
        <h2>Review</h2>
        <textarea placeholder="Write your review here..."></textarea>
    </div>
</div>

</body>
</html>
