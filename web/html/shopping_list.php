<?php 
require_once 'util.php'; // Include utility functions 
require_once 'nav.php'; 
session_start(); 

// Redirect if not logged in 
if (!isset($_SESSION['user_id'])) { 
    header('Location: login.php'); 
    exit(); 
}

// Get the user ID from the session
$user_id = $_SESSION['user_id'];

// Fetch the current shopping list
$api_path_shopping_list = "api/users/$user_id/shopping-list"; 
$shopping_list_response = api_request_with_token($api_path_shopping_list);
// var_dump($shopping_list_response);

// Check if the response was successful
if(isset($shopping_list_response['code']) && $shopping_list_response['code'] === 200 && isset($shopping_list_response['data']) && !isset($_GET['id']))
{
    $shopping_list_data = $shopping_list_response['data'];
    $ingredient_list = $shopping_list_data['ingredients'];
    $shopping_list_r = $shopping_list_data['raw_recipes'];
    
    $recipe_names = array();
    foreach($shopping_list_r as $recipe_id => $recipe_data)
    {
            if(isset($recipe_data['title']))
            {
                array_push($recipe_names, $recipe_data['title']);
            }
    }
    
}
elseif (isset($shopping_list_response['code']) && $shopping_list_response['code'] === 200 && isset($shopping_list_response['data']) && isset($_GET['id'])) 
{
    $new_recipe_id = htmlspecialchars($_GET['id']);
    $shopping_list_data = $shopping_list_response['data'];

  
    $curr_recipes = $shopping_list_data['recipes'];
    $curr_ingredients = $shopping_list_data['ingredients'];
    $recipes = $shopping_list_data['raw_recipes'];
    array_push($curr_recipes,$new_recipe_id);
   

    $api_path_recipe = "api/recipes/$new_recipe_id";
    $recipe_response = api_request_with_token($api_path_recipe);
   
    if (isset($recipe_response['code']) && $recipe_response['code'] === 200) 
   {
    $recipe_data = $recipe_response['data'];
    $new_ingredients = $recipe_data['ingredients'];
    

    // Aggregate ingredients
    foreach ($new_ingredients as $ingredient => $quantity) {
        if (isset($curr_ingredients[$ingredient])) {
             $newQ = parser($curr_ingredients[$ingredient],$quantity);
            // Update the current ingredients with the aggregated quantity
            $curr_ingredients[$ingredient] = $newQ;
        } else {
            // New ingredient, add to the current ingredients
            echo $curr_ingredients[$ingredient];
            $curr_ingredients[$ingredient] = $quantity;
        }
    }
    
    $ingredient_list = $curr_ingredients;
    
} else {
    echo "Failed to fetch recipe data. Error: ";
    exit();
}

$update_payload = [
    'recipes' => $curr_recipes,
    'ingredients' => $curr_ingredients,
];

$update_response = api_request_with_token("api/users/$user_id/shopping-list", 'PATCH', $update_payload);

if (isset($update_response['code']) && $update_response['code'] === 200) {
    header("Refresh:0; url=shopping_list.php");
} else {
    echo "Failed to update shopping list. Error: ";
    exit();
}
   }






if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_recipe_id'])) {
    $delete_recipe_id = htmlspecialchars($_POST['delete_recipe_id']);

    echo $delete_recipe_id;
    $api_path_shopping_list = "api/users/$user_id/shopping-list"; 
    $shopping_list_response = api_request_with_token($api_path_shopping_list);

    if(isset($shopping_list_response['code']) && $shopping_list_response['code'] === 200 && isset($shopping_list_response['data']) && !isset($_GET['id']))
    {
            $shopping_list_data = $shopping_list_response['data'];
            $curr_recipes = $shopping_list_data['recipes'];
            $curr_ingredients = $shopping_list_data['ingredients'];

         


            if (($key = array_search($delete_recipe_id, $curr_recipes)) !== false) {
                unset($curr_recipes[$key]);
            }

            $api_path_recipe = "api/recipes/$delete_recipe_id";
            $recipe_response = api_request_with_token($api_path_recipe);
            
    if (isset($recipe_response['code']) && $recipe_response['code'] === 200) 
    {

            $recipe_response_data=$recipe_response['data'];
            $recipe_ingredients = $recipe_response_data['ingredients'];

        foreach ($recipe_ingredients as $ingredient => $quantity) {
            if (isset($curr_ingredients[$ingredient])) {
                $curr_ingredients[$ingredient] = parser_sub($curr_ingredients[$ingredient], $quantity);
                // Remove the ingredient if its quantity is zero or less
                if ($curr_ingredients[$ingredient] === '0') {
                    unset($curr_ingredients[$ingredient]);
                }
            }
        }
    }



            
            







            $update_payload = [
                'recipes' => $curr_recipes,
                'ingredients' => $curr_ingredients,
            ];
            
            $update_response = api_request_with_token("api/users/$user_id/shopping-list", 'PATCH', $update_payload);

            if (isset($update_response['code']) && $update_response['code'] === 200) {
                header("Refresh:0; url=shopping_list.php");
            } else {
                echo "Failed to update shopping list. Error: ";
              // var_dump($update_response);
                exit();
            }

    }
    else
    {
        echo "Fail";
        var_dump($shopping_list_response);
    }
}
function parser($original,$added) {
    $po = explode(' ',$original);
    $pn = explode(' ',$added);

    $newQuantity = (float)$po[0] + (float)$pn[0];
    
    $newM = (string)$newQuantity . " " . (string)$po[1];
    // echo $newM . "<br>";
    
    return $newM;
}
function parser_sub($original, $to_subtract) {
    $original_parts = explode(' ', $original);
    $to_subtract_parts = explode(' ', $to_subtract);

    if ($original_parts[1] === $to_subtract_parts[1]) { // Check if units match
        $new_quantity = (float)$original_parts[0] - (float)$to_subtract_parts[0];
        return $new_quantity > 0 ? $new_quantity . ' ' . $original_parts[1] : '0';
    }

    // If units don't match, return the original quantity (to handle edge cases)
    return $original;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php echo($NAV_HEADERS); ?>
    <meta charset="UTF-8">
    <title>Shopping List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
        <?php if (!empty($recipe_names)): ?>
            <?php foreach ($recipe_names as $recipe): ?>
                <?php if (isset($recipe_data['title'])): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <?php echo htmlspecialchars($recipe); ?>
                        <form method="POST" action="shopping_list.php" style="display: inline;">
                            <input type="hidden" name="delete_recipe_id" value="<?php echo htmlspecialchars($recipe_id); ?>">
                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </li>
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
