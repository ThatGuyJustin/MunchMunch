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

if (!isset($_GET["id"])) {
    echo("Recipe not found.");
    exit();
} else {
    $recipe_id = $_GET["id"];
}

// Fetch recipe data
$recipe = api_request_with_token("api/recipes/$recipe_id");
if (!isset($recipe['data'])) {
    echo("Recipe not found.");
    exit();
}
$recipe = $recipe["data"];

// Fetch the user who created the recipe
$who_did_it = api_request_with_token("api/users/" . $recipe["user"]);

$recipe_image_url = "image.png"; 
$media_hash = null;
if(count($recipe["media"]["main"]) > 0){
    $media_hash = $recipe["media"]["main"][0];
    $recipe_image_url = "api/media/recipe/$recipe_id/main/$media_hash";
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
            text-align: center; /* Center align the contents */
        }
        .title-section h1 {
            font-size: 32px;
            margin: 0;
        }
        .author {
            font-size: 18px;
            color: gray;
        }
        .main-content img {
            width: 100%;
            max-width: 600px; /* Limit the width of the image */
            margin: 20px auto;
            display: block; /* Center the image */
            border-radius: 10px;
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

    <!-- Recipe Image -->
    <div class="main-content">
        <img src="<?php echo htmlspecialchars($recipe_image_url); ?>" alt="<?php echo htmlspecialchars($recipe["title"]); ?>">
    </div>

    <!-- Buttons Section -->
    <div class="buttons">
        <a href="recipe.php?id=<?php echo htmlspecialchars($recipe_id); ?>">
            <button>Cook!</button>
        </a>
        <button id="favorite_button">Save for later</button>
    </div>
</div>

</body>
</html>

