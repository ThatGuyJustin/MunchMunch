<?php
require_once 'util.php';
require_once 'nav.php';

start_session();

$error_message = '';
$success_message = '';

// Determine whether to fetch all tickets (admin) or only the user's tickets (non-admin)
$user_id = $_SESSION['user_id'] ?? null;
$is_admin = $_SESSION['is_admin'] ?? false; // Assuming this session variable indicates admin status

// Use the admin API endpoint to fetch tickets, but adjust parameters based on user role
$api_endpoint = $is_admin ? "api/admin/requests" : "api/admin/requests?user_id=$user_id";
$tickets = api_request_with_token($api_endpoint, "GET");

// Check if tickets were retrieved
if (!$tickets || !isset($tickets['data']) || count($tickets['data']) === 0) {
    $error_message = "No tickets found or failed to fetch tickets.";
}

// Get the current ticket ID from the URL parameter (if available)
$current_ticket_id = $_GET['ticket_id'] ?? null;

// Handle form submission for posting a response
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ticket_id = $_POST['ticket_id'];
    $response_message = $_POST['response_message'] ?? null;

    // Post a new response message to the ticket
    if ($response_message) {
        $response_data = ['message' => $response_message];
        $message_response = api_request_with_token("api/admin/requests/$ticket_id/messages", "POST", $response_data);
        if (isset($message_response['code']) && $message_response['code'] === 200) {
            $success_message = "Response posted successfully!";
            // Redirect to refresh the page and display the new response
            header("Location: user_tickets.php?ticket_id=$ticket_id");
            exit();
        } else {
            $error_message = "Failed to post response.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php echo($NAV_HEADERS) ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Ticket Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        function showTicket(ticketId) {
            const ticketDetails = document.getElementsByClassName('ticket-details');
            for (let i = 0; i < ticketDetails.length; i++) {
                ticketDetails[i].style.display = 'none';
            }
            document.getElementById('ticket-' + ticketId).style.display = 'block';
        }
        
        // Function to open the ticket passed in the URL parameter
        document.addEventListener("DOMContentLoaded", function() {
            const currentTicketId = <?php echo json_encode($current_ticket_id); ?>;
            if (currentTicketId) {
                showTicket(currentTicketId); // Open the specified ticket on page load
            } else {
                const firstTicket = document.querySelector('.ticket-details');
                if (firstTicket) {
                    firstTicket.style.display = 'block';
                }
            }
        });
    </script>
</head>
<body>
    <?php echo($NAV_ICONS) ?>

    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><?php echo $is_admin ? 'Admin' : 'User'; ?> Dashboard - My Tickets</h1>
            <button class="btn btn-secondary" onclick="window.location.href='dashboard.php'">Go to Main Screen</button>
        </div>

        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <div class="row">
            <!-- Sidebar: Ticket List -->
            <div class="col-md-3">
                <h4>Your Tickets</h4>
                <div class="list-group">
                    <?php if ($tickets && isset($tickets['data']) && count($tickets['data']) > 0): ?>
                        <?php foreach ($tickets['data'] as $ticket): ?>
                            <a href="#" class="list-group-item list-group-item-action" onclick="showTicket(<?php echo $ticket['id']; ?>)">
                                <?php echo htmlspecialchars($ticket['subject']); ?>
                                <br><small>Status: <?php echo htmlspecialchars($ticket['status']); ?></small>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No tickets found.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Main View: Ticket Details -->
            <div class="col-md-9">
                <?php if ($tickets && isset($tickets['data']) && count($tickets['data']) > 0): ?>
                    <?php foreach ($tickets['data'] as $ticket): ?>
                        <div id="ticket-<?php echo $ticket['id']; ?>" class="ticket-details" style="display: none;">
                            <h4><?php echo htmlspecialchars($ticket['subject']); ?></h4>
                            <p><strong>Submitted on:</strong> <?php echo htmlspecialchars($ticket['created_at']); ?></p>
                            <p><strong>Status:</strong> <?php echo htmlspecialchars($ticket['status']); ?></p>
                            <p><strong>Message:</strong> <?php echo htmlspecialchars($ticket['message']); ?></p>

                            <!-- Display list of message objects -->
                            <?php if (!empty($ticket['messages'])): ?>
                                <div class="responses mt-3">
                                    <h6>Messages:</h6>
                                    <?php foreach ($ticket['messages'] as $message): ?>
                                        <p><strong><?php echo htmlspecialchars($message['author'] ?? 'Unknown'); ?>:</strong> <?php echo htmlspecialchars($message['message']); ?></p>
                                        <p><small>Timestamp: <?php echo htmlspecialchars(date('Y-m-d H:i:s', $message['timestamp'])); ?></small></p>
                                        <hr>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <!-- Form to post a response message -->
                            <form action="user_tickets.php" method="post" class="mt-3">
                                <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">
                                <div class="mb-3">
                                    <label for="response_message-<?php echo $ticket['id']; ?>" class="form-label">Post a Response:</label>
                                    <textarea name="response_message" id="response_message-<?php echo $ticket['id']; ?>" class="form-control" rows="3"></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Submit Response</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

</body>
</html>
