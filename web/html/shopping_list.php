<?php
require_once 'util.php'; // Include utility functions
require_once 'nav.php';
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

/* // Test data for displaying the shopping list without API calls
// Mock data for shopping list response structure
$shopping_list_data = [
    "recipes" => [1, 2, 3], // List of recipe IDs
    "ingredients" => [
        "Spaghetti" => 200,
        "Ground Beef" => 500,
        "Tomato Sauce" => 1,
        "Lettuce" => 1,
        "Chicken Breast" => 300,
        "Taco Shells" => 6
    ],
    "raw_recipes" => [
        1 => ["name" => "Spaghetti Bolognese"],
        2 => ["name" => "Chicken Caesar Salad"],
        3 => ["name" => "Beef Tacos"]
    ]
];

// Initialize arrays to hold ingredients and recipes as in real code
$ingredient_list = [];
$shopping_list_recipes = [];

// Aggregate ingredients as in the real API structure
foreach ($shopping_list_data['ingredients'] as $ingredient => $quantity) {
    if (isset($ingredient_list[$ingredient])) {
        $ingredient_list[$ingredient] += $quantity; // Sum quantities of the same ingredient
    } else {
        $ingredient_list[$ingredient] = $quantity;
    }
}

// Extract recipes from raw_recipes using recipe IDs
foreach ($shopping_list_data['recipes'] as $recipe_id) {
    if (isset($shopping_list_data['raw_recipes'][$recipe_id])) {
        $shopping_list_recipes[] = $shopping_list_data['raw_recipes'][$recipe_id]; // Store recipe data
    }
} */

// Fetch the shopping list for the current user
$user_id = $_SESSION['user_id'];
$api_path_shopping_list = "api/users/$user_id/shopping-list";
$shopping_list_response = api_request_with_token($api_path_shopping_list);

// Check if the response was successful
if (isset($shopping_list_response['code']) && $shopping_list_response['code'] === 200 && isset($shopping_list_response['data'])) {
    $shopping_list_data = $shopping_list_response['data'];
    
    // Aggregate ingredient quantities
    foreach ($shopping_list_data['ingredients'] as $ingredient => $quantity) {
        if (isset($ingredient_list[$ingredient])) {
            $ingredient_list[$ingredient] += $quantity; // Sum quantities of the same ingredient
        } else {
            $ingredient_list[$ingredient] = $quantity;
        }
    }

    // Extract recipes from raw_recipes using recipe IDs in shopping list
    foreach ($shopping_list_data['recipes'] as $recipe_id) {
        if (isset($shopping_list_data['raw_recipes'][$recipe_id])) {
            $shopping_list_recipes[] = $shopping_list_data['raw_recipes'][$recipe_id]; // Store recipe data
        }
    }
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
            <h4>Ingredients to Buy</h4>
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
                <?php foreach ($shopping_list_recipes as $recipe): ?>
                    <li class="list-group-item">
                        <?php echo htmlspecialchars($recipe['name']); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>

</body>
</html>
