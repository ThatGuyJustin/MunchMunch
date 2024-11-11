<?php
require_once 'util.php'; // Include the utility functions
require_once 'nav.php';

start_session(); // Start session

if (!is_user_logged_in()) {  // Check if the user is logged in using the utility function
    header('Location: login.php');
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php echo($NAV_HEADERS) ?>
    <meta charset="UTF-8">
    <title>Welcome - FoodTinder</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <?php echo($NAV_ICONS) ?>
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
    <p>Thank you for registering. You can now explore and use all features of the platform.</p>
    <a href="dashboard.php">Go to Main Page</a>
</body>
</html>
