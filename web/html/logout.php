<?php
require_once 'util.php';

// Logout the user by clearing the session
logout_user();

// Redirect to the login page
header('Location: login.php');
exit();
?>
