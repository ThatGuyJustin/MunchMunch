<?php
require_once 'util.php'; // Include utility functions
require_once 'nav.php';

start_session();

// Redirect to login page if the user is not logged in
if (!is_user_logged_in()) {
    header('Location: login.php');
    exit();
}

$error_message = '';
$success_message = '';
$user_id = $_SESSION["user_id"];


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_favorites'])) {
    // Fetch the current user's favorites to add the new recipe
    $user_response = api_request_with_token("api/users/$user_id");
    
    if ($user_response && isset($user_response['data'])) {
        $user_data = $user_response['data'];
        $favorites = isset($user_data['favorite_posts']) ? $user_data['favorite_posts'] : [];

        // Add the current recipe to favorites if it's not already in the list
        if (!in_array($recipe_id, $favorites)) {
            $favorites[] = $recipe_id;
        }

        // Send the updated favorites list to the API
        $update_data = ['favorite_posts' => $favorites];
        $response = api_request_with_token("api/users/$user_id", "PATCH", $update_data);

        if ($response && $response['code'] === 200) {
            $success_message = "Recipe added to favorites!";
        } else {
            $error_message = isset($response['msg']) ? $response['msg'] : "Failed to add to favorites.";
        }
    } else {
        $error_message = "Failed to retrieve user data.";
    }
}
?>

