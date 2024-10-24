<?php
require_once 'util.php';

start_session();

$error_message = '';
$success_message = '';

// Check if the user is logged in
if (!is_user_logged_in()) {
    header('Location: login.php');
    exit();
}

// Get the logged-in user's details from the session
$user_name = isset($_SESSION['username']) ? $_SESSION['username'] : '';
$user_email = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : '';

$admin_email = "admin@foodtinder.com"; 

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize user inputs
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $message = trim($_POST['message']);
    $category_request = trim($_POST['category_request']);

    if (empty($name) || empty($email) || empty($message)) {
        $error_message = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Please enter a valid email address.";
    } else {
        $subject = "New Contact Request from $name";
        $body = "You have received a new contact request:\n\n" .
                "Name: $name\n" .
                "Email: $email\n" .
                "Message: $message\n\n" .
                "Category Request: $category_request\n\n";

        if (mail($admin_email, $subject, $body, "From: $email")) {
            $success_message = "Your message has been sent successfully!";
        } else {
            $error_message = "Failed to send your message. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - FoodTinder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h1>Contact Us</h1>
    <p>If you have any questions, requests for new categories, or need help, feel free to contact us below.</p>

    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
    <?php endif; ?>

    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <form action="contact.php" method="post">
        <div class="mb-3">
            <label for="name" class="form-label">Your Name:</label>
            <input type="text" name="name" class="form-control" id="name" value="<?php echo htmlspecialchars($user_name); ?>" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Your Email:</label>
            <input type="email" name="email" class="form-control" id="email" value="<?php echo htmlspecialchars($user_email); ?>" required>
        </div>
        <div class="mb-3">
            <label for="message" class="form-label">Message:</label>
            <textarea name="message" class="form-control" id="message" rows="5" required></textarea>
        </div>
        <div class="mb-3">
            <label for="category_request" class="form-label">Request New Category:</label>
            <input type="text" name="category_request" class="form-control" id="category_request" placeholder="Optional">
        </div>
        <button type="submit" class="btn btn-primary">Send Message</button>
    </form>
    <div class="mt-4">
    <button class="btn btn-secondary" onclick="window.location.href='dashboard.php'">Back to Dashboard</button>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
