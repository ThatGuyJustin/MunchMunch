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
$user_id = $_SESSION['user_id'];
$api_path_shopping_list = "api/users/$user_id/shopping-list";
$shopping_list_response = api_request_with_token($api_path_shopping_list);

// Check if the response was successful
if (isset($shopping_list_response['code']) && $shopping_list_response['code'] === 200 && isset($shopping_list_response['data'])) {
    $shopping_list_data = $shopping_list_response['data'];
    
    // Ensure both recipes and ingredients are set as arrays
    $shopping_list_recipes = isset($shopping_list_data['recipes']) ? $shopping_list_data['recipes'] : [];
    $ingredient_list = isset($shopping_list_data['ingredients']) ? $shopping_list_data['ingredients'] : [];
} else {
    echo "Failed to load shopping list. Error: ";
    var_dump($shopping_list_response); // Debug output
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php echo($NAV_HEADERS); ?>
    <meta charset="UTF-8">
    <title>Shopping List</title>
    
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
        <!-- Ingredient List on the left -->
        <div class="col-md-6 shopping-list-box">
            <h4>Ingredients</h4>
            <ul class="list-group">
                <?php if (!empty($ingredient_list)): ?>
                    <?php foreach ($ingredient_list as $ingredient => $quantity): ?>
                        <li class="list-group-item">
                            <?php echo htmlspecialchars($ingredient) . ": " . htmlspecialchars($quantity); ?>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="list-group-item">No ingredients in the shopping list.</li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- Recipe List on the right -->
        <div class="col-md-6 recipe-list-box">
            <h4>Included Recipes</h4>
            <ul class="list-group">
                <?php if (!empty($shopping_list_recipes)): ?>
                    <?php foreach ($shopping_list_recipes as $recipe_id): ?>
                        <!-- Fetch recipe details for each recipe in the list -->
                        <?php 
                        $recipe_data = api_request_with_token("api/recipes/$recipe_id");
                        if (isset($recipe_data['data'])): 
                        ?>
                            <li class="list-group-item">
                                <?php echo htmlspecialchars($recipe_data['data']['name']); ?>
                            </li>
                        <?php else: ?>
                            <li class="list-group-item">Recipe not found.</li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="list-group-item">No recipes in the shopping list.</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>
</body>
</html>
