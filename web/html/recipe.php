<?php
require_once 'util.php'; // Include utility functions
require_once 'nav.php';

start_session();

// Redirect to login page if the user is not logged in
if (!is_user_logged_in()) {
    header('Location: login.php');
    exit();
}

$error_message = '';
$success_message = '';

// Get the recipe ID from the URL query parameter
if (!isset($_GET["id"])) {
    echo "Recipe not found.";
    exit();
} else {
    $recipe_id = $_GET["id"];
}

$user_id = $_SESSION["user_id"];

api_request_with_token("api/users/$user_id/history", "POST", [
    "recipe" => $recipe_id,
    "type" => "VIEW",
]);

// Fetch the recipe details (without reviews) from the API
$recipe = api_request_with_token("api/recipes/$recipe_id");

if (!isset($recipe['data'])) {
    echo "Recipe not found.";
    exit();
}

$recipe = $recipe["data"];

// Fetch the user (who created the recipe) details
$who_did_it = api_request_with_token("api/users/" . $recipe["user"]);

// Fetch all tags to display in the recipe
$all_tags = api_request_with_token("api/tags");

$needed_tags = array();

foreach ($all_tags as &$tag) {
    if (in_array($tag["id"], $recipe["tags"])) {
        array_push($needed_tags, $tag["emoji"] . $tag["label"]);
    }
}

// Set up the image URL and author name based on recipe_id
$recipe_image_url = "image.png"; 
if (strpos($recipe_id, 'sp_') === 0 && isset($recipe["media"]["main"]) && count($recipe["media"]["main"]) > 0) {
    // hardcode the author
    $recipe_image_url = $recipe["media"]["main"][0];
    $author_name = "Spoonacular";
} else {
    // For other recipes, get image use the actual author's name
    $media_hash = $recipe["media"]["main"][0];
    $recipe_image_url = "api/media/recipe/$recipe_id/main/$media_hash";
    $author_name = $who_did_it["data"]["name"];
}

// Format the ingredients as a list
$ingredients = array();
foreach ($recipe["ingredients"] as $key => $value) {
    array_push($ingredients, htmlspecialchars($key) . ": " . htmlspecialchars($value));
}

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && strpos($recipe_id, 'sp_') !== 0) {
    $rating = intval($_POST['rating']);
    $comment = trim($_POST['comment']);

    // Prepare the review data
    $review_data = [
        'rating' => $rating,
        'comment' => $comment
    ];

    // Send the review to the API
    $response = api_request_with_token("api/recipes/$recipe_id/reviews", "POST", $review_data);

    // Check if the review submission was successful
    if ($response && $response['code'] === 200) {
        $success_message = "Review submitted successfully!";
        // Reload the page to show the new review
        header("Location: recipe.php?id=$recipe_id");
        exit();
    } else {
        $error_message = isset($response['msg']) ? $response['msg'] : "Failed to submit the review.";
    }
}

// Handle adding to shopping list
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_shopping_list'])) {
    $add_to_shopping_data = [
        'recipes' => [$recipe_id],
        'ingredients' => $recipe["ingredients"]
    ];

    $response = api_request_with_token("api/users/$user_id/shopping-list", "POST", $add_to_shopping_data);

    if ($response && $response['code'] === 200) {
        $success_message = "Recipe added to shopping list!";
    } else {
        $error_message = isset($response['msg']) ? $response['msg'] : "Failed to add to shopping list.";
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <?php echo($NAV_HEADERS) ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($recipe["title"]); ?></title>
    <!-- Include Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php echo($NAV_ICONS) ?>
    <div class="container mt-5">
        <!-- Recipe Title and Author -->
        <div class="row">
            <div class="col-10">
                <h1 class="display-4"><?php echo htmlspecialchars($recipe["title"]); ?></h1><p><?php echo implode(", ", $needed_tags); ?></p>
                <p class="lead">By: <?php echo htmlspecialchars($author_name); ?></p>
            </div>
            <div class="col-1">
                <img class="image-fluid" width=200 height=200 src="<?php echo htmlspecialchars($recipe_image_url); ?>" alt="<?php echo htmlspecialchars($recipe["title"]); ?>">
            </div>
        </div>

        <!-- Recipe Details -->
        <div class="row">
            <div class="col-md-6">
                <i class="fa-solid fa-stopwatch"></i> <strong>Time to Cook:</strong> <?php echo htmlspecialchars($recipe["time_to_cook"] / 60); ?> Minutes
            </div>
        </div>

        <?php if (strpos($recipe_id, 'sp_') !== 0): ?>
            <!-- Only show Time to Prepare and Skill Level if recipe_id does not start with id sp-->
            <div class="row">
                <div class="col-md-6">
                    <i class="fa-solid fa-stopwatch"></i> <strong>Time to Prepare:</strong> <?php echo htmlspecialchars($recipe["time_to_prepare"] / 60); ?> Minutes
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <i class="fa-solid fa-medal"></i> <strong>Skill Level:</strong> <?php echo htmlspecialchars($recipe["skill_level"]); ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="row">
            <p><strong>Steps:</strong></p>
            <ul class="list-group list-group-flush">
                <?php foreach ($recipe["steps"] as $step): ?>
                        <?php echo "<li class='list-group-item'>" . htmlspecialchars($step) . "</li>";?>
                <?php endforeach; ?>
            </ul>
            <p><strong>Ingredients:</strong></p>
            <ul class="list-group list-group-flush">
                <?php foreach ($ingredients as $ingredient): ?>
                        <?php echo "<li class='list-group-item'> " . htmlspecialchars($ingredient) . " </li>";?>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- add if for Sp -->
        <?php if (strpos($recipe_id, 'sp_') !== 0): ?>
            <!-- Review submission form -->
            <div class="row mt-5">
                <div class="col-12">
                    <h3>Leave a Review</h3>
                    <?php if (!empty($success_message)): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
                    <?php endif; ?>
                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
                    <?php endif; ?>
                    <form action="recipe.php?id=<?php echo htmlspecialchars($recipe_id); ?>" method="post">
                        <div class="mb-3">
                            <label for="rating" class="form-label">Rating:</label>
                            <input type="range" class="form-range" min="1" max="5" name="rating" id="rating" required>
                        </div>
                        <div class="mb-3">
                            <label for="comment" class="form-label">Comment:</label>
                            <textarea name="comment" class="form-control" rows="4" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit Review</button>
                    </form>
                </div>
            </div>

            <!-- Display existing reviews -->
            <div class="row mt-5">
                <div class="col-12">
                    <h3>User Reviews</h3>
                    <?php if (!empty($reviews)): ?>
                        <div class="list-group">
                            <?php foreach ($reviews as $review): ?>
                                <div class="list-group-item">
                                    <h5><?php echo htmlspecialchars($review["user"]["name"]); ?></h5>
                                    <p><?php echo htmlspecialchars($review["comment"]); ?></p>
                                    <p><strong>Rating:</strong> <?php echo htmlspecialchars($review["rating"]); ?> stars</p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>No reviews yet. Be the first to leave a review!</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Add to Shopping List Button (always visible) -->
        <div class="row mt-5">
            <div class="col-12">
                <form action="recipe.php?id=<?php echo htmlspecialchars($recipe_id); ?>" method="post">
                    <button type="submit" name="add_to_shopping_list" class="btn btn-success">Add to Shopping List</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
