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
        'ingredients' => $ingredients,
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
    <title>Upload Recipe</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #007bff;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            margin-bottom: 5px;
            font-weight: bold;
        }

        input, textarea, select, button {
            margin-bottom: 15px;
            padding: 10px;
            font-size: 16px;
            border-radius: 5px;
            border: 1px solid #ccc;
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

        /* Error and success messages */
        .message {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
        }

        .success-message {
            background-color: #d4edda;
            color: #155724;
        }

        /* Responsive */
        @media (max-width: 600px) {
            .container {
                padding: 15px;
            }
        }
    </style>
</head>
<body>

    <?php include 'nav.php'; ?> 
    <div class="container">
        <h1>Upload a Recipe</h1>


        <?php if (!empty($success_message)): ?>
            <div class="message success-message"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
            <div class="message error-message"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <form method="post">
            <label for="title">Recipe Title:</label>
            <input type="text" name="title" id="title" required>

            <label for="description">Description:</label>
            <textarea name="description" id="description" rows="4" required></textarea>

            <label for="tags">Tags (comma-separated IDs):</label>
            <input type="text" name="tags" id="tags" placeholder="e.g., 1,2,3" required>

            <label for="ingredient-name">Ingredient Name:</label>
            <input type="text" id="ingredient-name" placeholder="e.g., Water">

            <label for="ingredient-quantity">Quantity:</label>
            <input type="number" id="ingredient-quantity" placeholder="e.g., 2">

            <label for="ingredient-unit">Unit:</label>
            <select id="ingredient-unit">
                <option value="cups">Cups</option>
                <option value="teaspoons">Teaspoons</option>
                <option value="tablespoons">Tablespoons</option>
                <option value="grams">Grams</option>
                <option value="liters">Liters</option>
            </select>

            <button type="button" onclick="addIngredient()">Add Ingredient</button>

            <h3>Ingredients List</h3>
            <ul id="ingredient-list"></ul>

            <label for="steps">Steps (One per line):</label>
            <textarea name="steps" id="steps" rows="4" required></textarea>

            <label for="time_to_cook">Time to Cook (seconds):</label>
            <input type="number" name="time_to_cook" id="time_to_cook" required>

            <label for="time_to_prepare">Time to Prepare (seconds):</label>
            <input type="number" name="time_to_prepare" id="time_to_prepare" required>

            <label for="skill_level">Skill Level (1-10):</label>
            <input type="number" name="skill_level" id="skill_level" min="1" max="10" required>

            <button type="submit">Submit Recipe</button>
        </form>
    </div>

</body>
</html>