<?php
require_once 'util.php';
require_once 'nav.php';
start_session();

$error_message = '';
$success_message = '';

// Check if the user is logged in
if (!is_user_logged_in()) {
    header('Location: login.php');
    exit();
}

// Get the logged-in user's details from the session
$user_name = $_SESSION['username'] ?? '';
$user_id = $_SESSION['user_id'] ?? '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize user inputs
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    if (empty($subject) || empty($message)) {
        $error_message = "Subject and Message are required.";
    } else {
        // Prepare data with only required fields
        $data = [
            'subject' => $subject,
            'message' => $message
        ];
        
        // Send POST request to API to create a ticket
        $response = api_request_with_token('api/admin/requests', 'POST', $data);

        // Check if API response is successful without showing debug details to the user
        if ($response && isset($response['code']) && $response['code'] === 200 && isset($response['msg']) && strpos($response['msg'], 'Created') !== false) {
            $success_message = "Your request has been submitted successfully!";
        } else {
            // Log the response for debugging, but don't show detailed information on the frontend
            error_log("API Error: " . json_encode($response));
            $error_message = "Failed to submit your request. Please try again later.";
        }
    }
}

// Fetch existing tickets for the user
$requests = api_request_with_token("api/admin/requests?user_id=" . $user_id, "GET");

// Log errors for requests fetching, without displaying on the frontend
if ($requests === null) {
    error_log("API returned null response for GET /api/admin/requests?user_id=" . $user_id);
} else {
    error_log("Fetched requests: " . json_encode($requests));
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php echo($NAV_HEADERS) ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Support - FoodTinder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php echo($NAV_ICONS) ?>
    <div class="container mt-5">
        <h1>Contact Support</h1>
        <p>If you have any questions, requests for new categories, or need help, please submit a support ticket below.</p>

        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <!-- Ticket Submission Form -->
        <form action="contact.php" method="post">
            <div class="mb-3">
                <label for="subject" class="form-label">Subject:</label>
                <input type="text" name="subject" class="form-control" id="subject" required>
            </div>
            <div class="mb-3">
                <label for="message" class="form-label">Message:</label>
                <textarea name="message" class="form-control" id="message" rows="5" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Submit Ticket</button>
        </form>
        <div class="mt-4">
            <button class="btn btn-secondary" onclick="window.location.href='dashboard.php'">Back to Dashboard</button>
        </div>

        <!-- Display User's Requests -->
        <div class="container mt-5">
            <h2>Your Requests</h2>
            
            <?php if ($requests && isset($requests['data']) && count($requests['data']) > 0): ?>
                <div class="list-group">
                    <?php foreach ($requests['data'] as $request): ?>
                        <div class="list-group-item">
                            <h5 class="mb-1"><?php echo htmlspecialchars($request['subject']); ?></h5>
                            <p class="mb-1">Status: <?php echo htmlspecialchars($request['status']); ?></p>
                            <small>Assigned to: <?php echo htmlspecialchars($request['assigned_to'] ?? 'Not assigned'); ?></small>
                            <p class="mt-3"><?php echo htmlspecialchars($request['message']); ?></p>
                            
                            <!-- Display responses/messages for each request -->
                            <?php if (!empty($request['responses'])): ?>
                                <div class="responses mt-2">
                                    <h6>Responses:</h6>
                                    <?php foreach ($request['responses'] as $response): ?>
                                        <p><strong><?php echo htmlspecialchars($response['author']); ?>:</strong> <?php echo htmlspecialchars($response['message']); ?></p>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>No requests found.</p>
            <?php endif; ?>
        </div>

    </div>

</body>
</html>
