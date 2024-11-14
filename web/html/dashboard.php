<?php
require_once 'util.php';
require_once 'nav.php';

start_session();

// Redirect to login page if the user is not logged in
if (!is_user_logged_in()) {
    header('Location: login.php');
    exit();
}

// Fetch a random recipe from the API
$recipe = api_request_with_token("api/recipes/sp-random");
if (!isset($recipe['data'])) {
    echo "Recipe not found.";
    exit();
}

$recipe = $recipe["data"];

// Fetch the user who created the recipe
$who_did_it = api_request_with_token("api/users/" . $recipe["user"]);

// Fetch all tags to display in the recipe
$all_tags = api_request_with_token("api/tags");
$needed_tags = array();
foreach ($all_tags['data'] as &$tag) {
    if (in_array($tag["id"], $recipe["tags"])) {
        array_push($needed_tags, $tag["emoji"] . " " . $tag["label"]);
    }
}

// Set up the image URL
$recipe_image_url = "default_image.jpg"; 

// Check if recipe ID starts with sp_ and set image URL directly
if (strpos($recipe["id"], 'sp_') === 0 && isset($recipe["media"]["main"]) && count($recipe["media"]["main"]) > 0) {
    $recipe_image_url = $recipe["media"]["main"][0];  
} elseif (isset($recipe["media"]["main"]) && count($recipe["media"]["main"]) > 0) {
    $media_hash = $recipe["media"]["main"][0];
    $recipe_image_url = "api/media/recipe/" . $recipe["id"] . "/main/" . $media_hash;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php echo($NAV_HEADERS) ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Random Recipe - MunchMunch</title>
    <script src="js/scripts.js"></script>
    <style>
        .container {
            max-width: 800px;
            margin: 50px auto;
            position: relative;
        }
        .card {
            position: relative;
        }
        .card-img-top {
            height: 400px;
            object-fit: cover;
        }
        .button-container {
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            display: flex;
            justify-content: space-between;
            transform: translateY(-50%);
        }
        .button-left,
        .button-right {
            font-size: 1.2em;
            padding: 10px 20px;
            color: white;
            background-color: #007bff;
            border: none;
            cursor: pointer;
        }
        .button-left {
            margin-left: -120px;
        }
        .button-right {
            margin-right: -120px;
        }
    </style>
</head>
<body>

    <?php echo($NAV_ICONS) ?>

    <div class="container mt-5">
        <div class="card">
            <img src="<?php echo htmlspecialchars($recipe_image_url); ?>" class="card-img-top" alt="Recipe Image">
            <div class="card-body text-center">
                <h5 class="card-title">
                    <a href="recipe.php?id=<?php echo htmlspecialchars($recipe["id"]); ?>">
                        <?php echo htmlspecialchars($recipe["title"]); ?>
                    </a>
                </h5>
                <p class="card-text"><strong>By:</strong> 
                    <?php 
                    // Display Spoonacular 
                    echo strpos($recipe["id"], 'sp_') === 0 ? "Spoonacular" : htmlspecialchars($who_did_it["data"]["name"]); 
                    ?>
                </p>
                <p class="card-text"><strong>Tags:</strong> <?php echo implode(", ", $needed_tags); ?></p>
            </div>
        </div>
        <div class="button-container">
            <button onclick="fetchRandomRecipe('prev')" class="button-left">Previous</button>
            <button onclick="fetchRandomRecipe('next')" class="button-right">Next</button>
        </div>
    </div>

    <script>
        function fetchRandomRecipe(direction) {
            window.location.reload(); // Simply reloads the page for now
        }
    </script>
</body>
</html>
