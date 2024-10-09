<?php
require_once 'util.php'; // Include utility functions

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

start_session();

if (!is_user_logged_in()) {
    header('Location: login.php');
    exit();
}

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve user inputs
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $tags = isset($_POST['tags']) ? explode(",", $_POST['tags']) : [];
    $steps = isset($_POST['steps']) ? explode("\n", trim($_POST['steps'])) : [];

    // Handle ingredients as an object (key-value pairs)
    $ingredients = [];
    if (isset($_POST['ingredients'])) {
        foreach ($_POST['ingredients'] as $ingredient) {
            $ingredientArray = json_decode($ingredient, true); // Decode each ingredient JSON string
            $ingredients = array_merge($ingredients, $ingredientArray); // Merge each ingredient into the main object
        }
    }

    $time_to_cook = intval($_POST['time_to_cook'] ?? 0);
    $time_to_prepare = intval($_POST['time_to_prepare'] ?? 0);
    $skill_level = intval($_POST['skill_level'] ?? 1);
    $user_id = $_SESSION['user_id'];

    // Prepare data for the API request
    $data = [
        'user' => $user_id,
        'title' => $title,
        'description' => $description,
        'tags' => $tags,
        'steps' => $steps,
        'ingredients' => $ingredients, // Ingredients as a single object
        'time_to_cook' => $time_to_cook,
        'time_to_prepare' => $time_to_prepare,
        'skill_level' => $skill_level
    ];

    // API URL
    $api_url = "http://backend:5000/api/recipes"; // Correct API endpoint

    // Initialize cURL
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Follow redirects

    // Execute the request
    $response = curl_exec($ch);

    // Check for cURL errors
    if ($response === false) {
        $error_message = 'Curl error: ' . curl_error($ch);
    } else {
        $result = json_decode($response, true);

        if (isset($result['code']) && $result['code'] === 200) {
            $success_message = "Recipe submitted successfully!";
        } else {
            $error_message = "Error submitting recipe. API response: " . json_encode($result);
        }
    }

    curl_close($ch);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <!-- Link to the external JavaScript file -->
    <script src="js/scripts.js"></script> 
</head>
<body>

    <?php include 'nav.php'; ?> <!-- Navigation bar -->

    <h1>Upload a Recipe</h1>

    <!-- Show success or error messages -->
    <?php if (!empty($success_message)): ?>
        <p style="color: green;"><?php echo htmlspecialchars($success_message); ?></p>
    <?php endif; ?>
    <?php if (!empty($error_message)): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error_message); ?></p>
    <?php endif; ?>

    <form method="post">
        <div>
            <label for="title">Recipe Title:</label>
            <input type="text" name="title" id="title" required>
        </div>

        <div>
            <label for="description">Description:</label>
            <textarea name="description" id="description" required></textarea>
        </div>

        <!-- Tags Input Section -->
        <div>
            <label for="tags">Tags (comma-separated IDs):</label>
            <input type="text" name="tags" id="tags" placeholder="e.g., 1,2,3" required>
        </div>

        <!-- Ingredient Input Section -->
        <div>
            <label for="ingredient-name">Ingredient Name:</label>
            <input type="text" id="ingredient-name" placeholder="e.g., Water">
        </div>

        <div>
            <label for="ingredient-quantity">Quantity:</label>
            <input type="number" id="ingredient-quantity" placeholder="e.g., 2">
        </div>

        <div>
            <label for="ingredient-unit">Unit:</label>
            <select id="ingredient-unit">
                <option value="cups">Cups</option>
                <option value="teaspoons">Teaspoons</option>
                <option value="tablespoons">Tablespoons</option>
                <option value="grams">Grams</option>
                <option value="liters">Liters</option>
                <!-- Add more units as needed -->
            </select>
        </div>

        <button type="button" onclick="addIngredient()">Add Ingredient</button>

        <h3>Ingredients List</h3>
        <ul id="ingredient-list"></ul> <!-- Ingredient list to display added items -->

        <!-- Steps -->
        <div>
            <label for="steps">Steps (One per line):</label>
            <textarea name="steps" id="steps" required></textarea>
        </div>

        <div>
            <label for="time_to_cook">Time to Cook (seconds):</label>
            <input type="number" name="time_to_cook" id="time_to_cook" required>
        </div>

        <div>
            <label for="time_to_prepare">Time to Prepare (seconds):</label>
            <input type="number" name="time_to_prepare" id="time_to_prepare" required>
        </div>

        <div>
            <label for="skill_level">Skill Level (1-10):</label>
            <input type="number" name="skill_level" id="skill_level" min="1" max="10" required>
        </div>

        <button type="submit">Submit Recipe</button>
    </form>

</body>
</html>

</body>
</html>