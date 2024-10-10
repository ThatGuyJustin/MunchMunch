<?php
// util.php

// Start the session if it hasn't already been started
function start_session() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

// Set session variables (user details and token)
function set_user_session($user_id, $username, $token) {
    start_session();
    $_SESSION['user_id'] = $user_id;
    $_SESSION['username'] = $username;
    $_SESSION['token'] = $token; // Store token if needed
}

// Function to register a user
function register_user($username, $email, $password) {
    $data = [
        'username' => $username,
        'email' => $email,
        'password' => $password,
    ];

    // Prepare API URL dynamically based on the current host
    $api_url = "http://" . $_SERVER['HTTP_HOST'] . "/api/register";

    // Initialize cURL for making the request
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

    // Execute the request and fetch the response
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response === false) {
        return false; // Return false if there is an error
    }

    // Decode the JSON response
    return json_decode($response, true);
}

// util.php

// Function to check if the user is logged in (valid session)
function is_user_logged_in() {
    start_session();
    return isset($_SESSION['user_id']) && isset($_SESSION['token']);
}


// Logout function to clear the session
function logout_user() {
    start_session();
    session_unset();
    session_destroy();
}

// Function to authenticate the user via API and return the token
function authenticate_user($username, $password) {
    $data = [
        'login' => $username,
        'password' => $password,
    ];

    // Prepare API URL dynamically based on the current host
    $api_url = "http://" . $_SERVER['HTTP_HOST'] . "/api/login";

    // Initialize cURL for making the request
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

    // Execute the request and fetch the response
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response === false) {
        return false; // Return false if there is an error
    }

    // Decode the JSON response
    return json_decode($response, true);
}

// Function to validate token for subsequent API requests
function api_request_with_token($path, $method = 'GET', $data = null) {
    $url = getenv("HTTP_HOST") . "/" . $path;
    start_session();
    
    // Check if token exists in the session
    if (!isset($_SESSION['token'])) {
        return false; // No token, return false
    }
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    switch($method){
        case 'POST':
            curl_setopt($ch, CURLOPT_POST, true);
            break;
        case 'PATCH':
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
            break;
        case 'PUT':
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            break;
        case 'DELETE':
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            break;
        default:
            break;
    }

    // Set JSON data to be sent.
    if (!is_null($data)){
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    // Add the Authorization header with the token
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $_SESSION['token']
    ]);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}
?>
