<?php
require_once 'util.php'; // Include utility functions
require_once 'nav.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user data from the API
$api_path = 'api/users/self';
$response = api_request_with_token($api_path);
$user = $response["data"];

// Get uploaded recipes from the API
$uploaded_recipes = api_request_with_token("api/recipes?user_id=" . $user_id)["data"];

// Get favorited recipes from the API (assuming the API supports favorites endpoint)
$favorited_recipes = api_request_with_token("api/users/{$user_id}/favorites")["data"];

// Get viewed recipes (history) from the API
$viewed_recipes = api_request_with_token("api/users/{$user_id}/history")["data"];

// Define the profile image URL
$profile_image_url = "/api/media/avatars/" . $_SESSION['user_id'] . "/" . htmlspecialchars($user['avatar']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php echo($NAV_HEADERS) ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Account - FoodTinder</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/bootstrap.css">
    <style>
        body {
            background-color: #f8f9fa; /* Light gray background */
        }
        .profile-section {
            margin-top: 30px;
        }
        .profile-img {
            width: 150px;
            height: 150px;
            object-fit: cover;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .tab-content {
            margin-top: 20px;
        }
        .nav-tabs .nav-link.active {
            background-color: #007bff;
            color: white;
        }
        .nav-tabs .nav-link {
            border-radius: 0;
            color: #007bff;
        }
        .nav-tabs .nav-link:hover {
            background-color: #e9ecef;
        }
        .list-group-item {
            background-color: #fff;
            border: none;
            padding: 15px;
            border-bottom: 1px solid #e9ecef;
        }
        .list-group-item:last-child {
            border-bottom: none;
        }
        .list-group-item strong {
            font-size: 1.1rem;
        }
        .btn-follow {
            background-color: #28a745;
            color: white;
        }
        .btn-follow:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <?php echo($NAV_ICONS) ?>
    <div class="container profile-section">
        <div class="row">
            <!-- User information box (left column) -->
            <div class="col-md-4 mb-4">
                <div class="card text-center">
                    <div class="card-body">
                        <img src="<?php echo $profile_image_url; ?>" class="rounded-circle profile-img mb-3" alt="Profile Image">
                        <h4 class="card-title"><?php echo htmlspecialchars($user['name']); ?></h4>
                        <p class="text-muted">@<?php echo htmlspecialchars($user['username']); ?></p>
                        <p><strong>Dietary Preferences:</strong> <?php echo htmlspecialchars($user['preferences']); ?></p>
                        <button class="btn btn-follow">Follow</button>
                    </div>
                </div>
            </div>

            <!-- Tabs section (right column) -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <!-- Bootstrap Nav Tabs -->
                        <ul class="nav nav-tabs" id="recipeTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="uploaded-tab" data-bs-toggle="tab" data-bs-target="#uploaded" type="button" role="tab" aria-controls="uploaded" aria-selected="true">Recipes Uploaded</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="favorited-tab" data-bs-toggle="tab" data-bs-target="#favorited" type="button" role="tab" aria-controls="favorited" aria-selected="false">Recipes Favorited</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="history-tab" data-bs-toggle="tab" data-bs-target="#history" type="button" role="tab" aria-controls="history" aria-selected="false">History</button>
                            </li>
                        </ul>
                        <div class="tab-content" id="recipeTabContent">
                            <!-- Uploaded Recipes -->
                            <div class="tab-pane fade show active" id="uploaded" role="tabpanel" aria-labelledby="uploaded-tab">
                                <ul class="list-group list-group-flush">
                                    <?php if (!empty($uploaded_recipes)): ?>
                                        <?php foreach ($uploaded_recipes as $recipe): ?>
                                            <li class="list-group-item">
                                                <strong><?php echo htmlspecialchars($recipe['title']); ?></strong> 
                                                <span class="text-muted">(Uploaded on <?php echo htmlspecialchars($recipe['created_at']); ?>)</span>
                                            </li>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <li class="list-group-item">No recipes uploaded yet.</li>
                                    <?php endif; ?>
                                </ul>
                            </div>

                            <!-- Favorited Recipes -->
                            <div class="tab-pane fade" id="favorited" role="tabpanel" aria-labelledby="favorited-tab">
                                <ul class="list-group list-group-flush">
                                    <?php if (!empty($favorited_recipes)): ?>
                                        <?php foreach ($favorited_recipes as $recipe): ?>
                                            <li class="list-group-item">
                                                <strong><?php echo htmlspecialchars($recipe['title']); ?></strong> 
                                                <span class="text-muted">(Favorited on <?php echo htmlspecialchars($recipe['favorited_at']); ?>)</span>
                                            </li>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <li class="list-group-item">No recipes favorited yet.</li>
                                    <?php endif; ?>
                                </ul>
                            </div>

                            <!-- Viewed Recipes (History) -->
                            <div class="tab-pane fade" id="history" role="tabpanel" aria-labelledby="history-tab">
                                <ul class="list-group list-group-flush">
                                    <?php if (!empty($viewed_recipes)): ?>
                                        <?php foreach ($viewed_recipes as $recipe): ?>
                                            <li class="list-group-item">
                                                <strong><?php echo htmlspecialchars($recipe['title']); ?></strong> 
                                                <span class="text-muted">(Viewed on <?php echo htmlspecialchars($recipe['viewed_at']); ?>)</span>
                                            </li>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <li class="list-group-item">No recently viewed recipes.</li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
