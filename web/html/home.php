<?php
require_once 'util.php';

start_session();

// Redirect to login page if the user is not logged in
if (!is_user_logged_in()) {
    header('Location: login.php');
    exit();
}

// Fetch a random recipe from the API
$recipe = api_request_with_token("api/recipes/random");
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
$recipe_image_url = "default_image.jpg"; // Default image if none found
$media_hash = null;
if (isset($recipe["media"]["main"]) && count($recipe["media"]["main"]) > 0) {
    $media_hash = $recipe["media"]["main"][0];
    $recipe_image_url = "api/media/recipe/" . $recipe["id"] . "/main/" . $media_hash;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Random Recipe - MunchMunch</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container {
            max-width: 600px;
            margin: 50px auto;
        }
        .card-img-top {
            height: 300px;
            object-fit: cover;
        }
        .button-container {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <div class="card">
        <img src="<?php echo htmlspecialchars($recipe_image_url); ?>" class="card-img-top" alt="Recipe Image">
        <div class="card-body text-center">
            <!-- Recipe Title Link -->
            <h5 class="card-title">
                <a href="recipe.php?id=<?php echo htmlspecialchars($recipe["id"]); ?>">
                    <?php echo htmlspecialchars($recipe["title"]); ?>
                </a>
            </h5>
            <p class="card-text"><strong>By:</strong> <?php echo htmlspecialchars($who_did_it["data"]["name"]); ?></p>
            <p class="card-text"><strong>Tags:</strong> <?php echo implode(", ", $needed_tags); ?></p>
        </div>
    </div>
    <div class="button-container">
        <button onclick="fetchRandomRecipe('prev')" class="btn btn-primary">Previous</button>
        <button onclick="fetchRandomRecipe('next')" class="btn btn-primary">Next</button>
        <a href="recipe.php?id=<?php echo htmlspecialchars($recipe_id); ?>">
            <button>Cook!</button>
        </a>
    </div>
</div>
