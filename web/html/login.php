<?php
session_start();

// Check if the user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error_message = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize user inputs
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Prepare data for API request
    $data = [
        'login' => $username,
        'password' => $password,
    ];

    // Flask backend URL for login
    $api_url = $_SERVER['HTTP_HOST'] . "/api/register";

    // Initialize cURL
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

    // Execute the request
    $response = curl_exec($ch);

    if ($response === false) {
        $error_message = 'Error communicating with authentication server.';
    } else {
        $result = json_decode($response, true);
        if (isset($result['code']) && $result['code'] === 200) {
            // Store user data in session on successful login
            $_SESSION['user_id'] = $result['user']['id'];
            $_SESSION['username'] = $result['user']['username'];
            header('Location: dashboard.php');
            exit();
        } else {
            $error_message = isset($result['msg']) ? $result['msg'] : 'Invalid username or password.';
        }
    }

    curl_close($ch);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MunchMunch - Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
        }
        .container {
            width: 400px;
            margin: 100px auto;
            padding: 20px;
            background-color: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            font-size: 32px;
            text-align: center;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }
        .form-group button {
            width: 100%;
            padding: 10px;
            background-color: #28a745;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        .register-link {
            margin-top: 15px;
            text-align: center;
        }
        .register-link a {
            color: #007bff;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Login to FoodTinder</h1>

    <div class="form-group">
        <label for="username">Username</label>
        <input type="text" id="username" placeholder="Enter your username">
    </div>
    <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" placeholder="Enter your password">
    </div>
    <div class="form-group">
        <button onclick="loginUser()">Log In</button>
    </div>

    <!-- Link to the registration page -->
    <div class="register-link">
        <p>Don't have an account? <a href="register.php">Register here</a></p>
    </div>
</div>

<script>
    function loginUser() {
        alert("Login button clicked!");
        // Implement your login logic here
    }
</script>

</body>
</html>