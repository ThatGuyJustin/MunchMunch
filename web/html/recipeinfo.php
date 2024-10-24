<?php
require_once 'util.php'; // Include utility functions

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
foreach ($all_tags['data'] as &$tag) {
    if (in_array($tag["id"], $recipe["tags"])) {
        array_push($needed_tags, $tag["emoji"] . " " . $tag["label"]);
    }
}

// Fetch the reviews for the recipe from the API
$reviews_response = api_request_with_token("api/recipes/$recipe_id/reviews");
$reviews = isset($reviews_response['data']) ? $reviews_response['data'] : [];

// Format the ingredients as a list
$ingredients = array();
foreach ($recipe["ingredients"] as $key => $value) {
    array_push($ingredients, "<li>" . htmlspecialchars($key) . ": " . htmlspecialchars($value) . "</li>");
}

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        header("Location: recipeinfo.php?id=$recipe_id");
        exit();
    } else {
        $error_message = isset($response['msg']) ? $response['msg'] : "Failed to submit the review.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($recipe["title"]); ?></title>
    <!-- Include Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <!-- Recipe Title and Author -->
    <div class="row">
        <div class="col-12">
            <h1 class="display-4"><?php echo htmlspecialchars($recipe["title"]); ?></h1>
            <p class="lead">By: <?php echo htmlspecialchars($who_did_it["data"]["name"]); ?></p>
        </div>
    </div>

    <!-- Recipe Details -->
    <div class="row">
        <div class="col-md-6">
            <p><strong>Tags:</strong> <?php echo implode(", ", $needed_tags); ?></p>
            <p><strong>Time to Cook:</strong> <?php echo htmlspecialchars($recipe["time_to_cook"] / 60); ?> Minutes</p>
            <p><strong>Time to Prepare:</strong> <?php echo htmlspecialchars($recipe["time_to_prepare"] / 60); ?> Minutes</p>
            <p><strong>Skill Level:</strong> <?php echo htmlspecialchars($recipe["skill_level"]); ?></p>
            <p><strong>Steps:</strong></p>
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><?php echo implode("</li><li class='list-group-item'>", $recipe["steps"]); ?></li>
            </ul>
            <p><strong>Ingredients:</strong></p>
            <ul class="list-group list-group-flush">
                <?php echo implode("", $ingredients); ?>
            </ul>
        </div>
    </div>

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
            <form action="recipeinfo.php?id=<?php echo htmlspecialchars($recipe_id); ?>" method="post">
                <div class="mb-3">
                    <label for="rating" class="form-label">Rating (1-5):</label>
                    <input type="number" name="rating" class="form-control" min="1" max="5" required>
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
                            <h5><?php echo htmlspecialchars($review["user"]["name"]); ?></h5> <!-- User name from backend -->
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
</div>

<!-- Include Bootstrap JS and Popper.js -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
