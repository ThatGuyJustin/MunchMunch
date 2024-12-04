<?php
require_once 'util.php'; // Include utility functions
require_once 'nav.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$is_error = false;

// Initialize search parameters
$type = isset($_GET['type']) ? htmlspecialchars($_GET['type']) : 'recipe'; // Default to "recipe"
$query = isset($_GET['query']) ? htmlspecialchars($_GET['query']) : '';
$ingredients = isset($_GET['ingredients']) ? htmlspecialchars($_GET['ingredients']) : '';

// Adjust type for recipes
if ($type === 'recipes') {
    $type = 'recipe'; // Convert "recipes" to "recipe"
}

// Build API endpoint based on input
$api_path_search = "api/search?type=$type";
if ($query) $api_path_search .= "&query=$query";
if ($ingredients) $api_path_search .= "&ingredients=$ingredients";

$search_results = [];
$response = api_request_with_token($api_path_search);

if ($response['code'] != 200) {
    $is_error = true;
} else {
    $search_results = $response['data'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php echo($NAV_HEADERS); ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        .search-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }
        .search-container h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #007bff;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"], button {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        .results {
            margin-top: 30px;
        }
        .result-item {
            display: flex;
            align-items: center;
            background-color: #f1f1f1;
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 5px;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
        }
        .result-item img {
            width: 50px;
            height: 50px;
            margin-right: 15px;
            border-radius: 5px;
            object-fit: cover;
        }
        .result-item p {
            margin: 0;
            font-weight: bold;
            color: #333;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php echo($NAV_ICONS); ?>

    <!-- Search Form -->
    <div class="search-container">
        <h1>Search</h1>
        <form method="GET" action="search.php">
            <div class="form-group">
                <label for="query">Query</label>
                <input type="text" id="query" name="query" placeholder="Search (e.g., noodles)" value="<?php echo $query; ?>">
            </div>
            <div class="form-group">
                <label for="ingredients">Ingredients</label>
                <input type="text" id="ingredients" name="ingredients" placeholder="Ingredients (e.g., potatoes, tomatoes)" value="<?php echo $ingredients; ?>">
            </div>
            <div class="form-group">
                <label>Search Type</label>
                <label>
                    <input type="radio" name="type" value="users" <?php echo $type == 'users' ? 'checked' : ''; ?>> User
                </label>
                <label>
                    <input type="radio" name="type" value="recipe" <?php echo $type == 'recipe' ? 'checked' : ''; ?>> Recipe
                </label>
            </div>
            <button type="submit">Search</button>
        </form>

        <!-- Results -->
        <div class="results">
            <?php if ($is_error): ?>
                <p class="text-danger">An error occurred while fetching search results. Please try again.</p>
            <?php elseif (empty($search_results)): ?>
                <p>No results found.</p>
            <?php else: ?>
                <?php foreach ($search_results as $item): ?>
                    <div class="result-item">
                        <?php if ($type === 'recipe'): ?>
                            <img src="<?php echo $item['image'] ?? 'default-recipe.png'; ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                            <p><?php echo htmlspecialchars($item['title']); ?></p>
                        <?php else: ?>
                            <p><?php echo htmlspecialchars($item['name']); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
