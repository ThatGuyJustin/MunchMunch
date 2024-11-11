<?php
require_once 'util.php'; // Include utility functions
require_once 'nav.php';
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Initialize variables for storing recipes and ingredients
$shopping_list_recipes = [];
$ingredient_list = [];

// Fetch the shopping list for the current user
$api_path_shopping_list = "api/users/" . $_SESSION["user_id"] . "/shopping-list";
$shopping_list_response = api_request_with_token($api_path_shopping_list);

if ($shopping_list_response['code'] === 200) {
    $shopping_list_data = $shopping_list_response['data'];
    $shopping_list_recipeIDs = $shopping_list_data['recipes']; // List of recipe IDs
    $ingredient_list = $shopping_list_data['ingredients']; 
} else {
    echo "Failed to load shopping list.";
    exit();
}
$api_path_shopping_list = "api/users/" . $_SESSION["user_id"] . "/recipes";
$shopping_list_response = api_request_with_token($api_path_shopping_list);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php echo($NAV_HEADERS); ?>
    <meta charset="UTF-8">
    <title>Shopping List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .shopping-list-box, .recipe-list-box {
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 10px;
            background-color: #f8f9fa;
            margin-top: 20px;
        }
        .centered-title {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
<?php echo($NAV_ICONS); ?>


<div class="container mt-5">
    <h2 class="centered-title">Shopping List</h2>
    <div class="row">
        <!-- Ingredient List -->
        <div class="col-md-8 shopping-list-box">
            <h4>Ingredients</h4>
            <ul class="list-group">
                <?php foreach ($ingredient_list as $ingredient => $quantity): ?>
                    <li class="list-group-item">
                        <?php echo htmlspecialchars($ingredient) . ": " . htmlspecialchars($quantity); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        
       
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
