<?php
require_once 'util.php';
require_once 'nav.php';

start_session();

$error_message = '';
$success_message = '';

// Get all ticket requests
$tickets = api_request_with_token("api/admin/requests", "GET");

$current_ticket_id = $_GET['ticket_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ticket_id = $_POST['ticket_id'] ?? null; 

    if ($ticket_id) { 
        $status = $_POST['status'] ?? null;
        $assigned_to = $_POST['assigned_to'] ?? null;
        $response_message = $_POST['response_message'] ?? null;

        // Prepare data for updating the ticket
        $update_data = [];
        if ($status) $update_data['status'] = $status;
        if ($assigned_to) $update_data['assigned_to'] = $assigned_to;

        // Update ticket details
        if (!empty($update_data)) {
            $update_response = api_request_with_token("api/admin/requests/$ticket_id", "PATCH", $update_data);

            error_log("Update response: " . json_encode($update_response));

            if (isset($update_response['code']) && $update_response['code'] === 200) {
                $success_message = "Ticket updated successfully!";
            } else {
                $error_message = "Failed to update ticket. API Response: " . json_encode($update_response);
            }
        }

        // Post a new response message to the ticket
        if ($response_message) {
            $response_data = ['message' => $response_message];
            $message_response = api_request_with_token("api/admin/requests/$ticket_id/messages", "POST", $response_data);

            error_log("Message Response: " . json_encode($message_response));

            if (isset($message_response['code']) && $message_response['code'] === 200) {
                $success_message = "Response posted successfully!";
                // Redirect to refresh the page and keep the current ticket open
                header("Location: admin.php?ticket_id=$ticket_id");
                exit();
            } else {
                $error_message = "Failed to post response. API Response: " . json_encode($message_response);
            }
        }
    } else {
        $error_message = "Ticket ID is missing.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php echo($NAV_HEADERS) ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Ticket Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        function showTicket(ticketId) {
            const ticketDetails = document.getElementsByClassName('ticket-details');
            for (let i = 0; i < ticketDetails.length; i++) {
                ticketDetails[i].style.display = 'none';
            }
            
            // Show selected ticket details
            document.getElementById('ticket-' + ticketId).style.display = 'block';

            // Set the ticket ID in the hidden input for update and response forms
            document.getElementById('ticket-id-update').value = ticketId;
            document.getElementById('ticket-id-response').value = ticketId;
        }
        
        // Open the ticket passed in the URL parameter on page load
        document.addEventListener("DOMContentLoaded", function() {
            const currentTicketId = <?php echo json_encode($current_ticket_id); ?>;
            if (currentTicketId) {
                showTicket(currentTicketId); 
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
            <h1>Admin Dashboard - Ticket Management</h1>
            <button class="btn btn-secondary" onclick="window.location.href='dashboard.php'">Go to Main Screen</button>
        </div>

        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-3">
                <h4>Tickets</h4>
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

            <div class="col-md-6">
                <?php if ($tickets && isset($tickets['data']) && count($tickets['data']) > 0): ?>
                    <?php foreach ($tickets['data'] as $ticket): ?>
                        <div id="ticket-<?php echo $ticket['id']; ?>" class="ticket-details" style="display: none;">
                            <h4><?php echo htmlspecialchars($ticket['subject']); ?></h4>
                            <p><strong>Submitted by:</strong> <?php echo htmlspecialchars($ticket['user_name']); ?></p>
                            <p><strong>Message:</strong> <?php echo htmlspecialchars($ticket['message']); ?></p>

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

                            <form action="admin.php" method="post" class="mt-3">
                                <input type="hidden" name="ticket_id" id="ticket-id-response">
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

            <!-- Update ticket section on the right -->
            <div class="col-md-3">
                <div class="update-ticket-section mt-3">
                    <h5>Update Ticket</h5>
                    <form action="admin.php" method="post">
                        <input type="hidden" name="ticket_id" id="ticket-id-update">

                        <div class="mb-3">
                            <label for="status-update" class="form-label">Update Status:</label>
                            <select name="status" id="status-update" class="form-select">
                                <option value="open">Open</option>
                                <option value="in_progress">In Progress</option>
                                <option value="closed">Closed</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="assigned-to-update" class="form-label">Assign to:</label>
                            <select name="assigned_to" id="assigned-to-update" class="form-select">
                                <?php foreach (api_request_with_token("api/search?type=admins")["data"] as $admin): ?>
                                    <option value="<?php echo htmlspecialchars($admin["id"]); ?>"><?php echo htmlspecialchars($admin["name"]); ?>(<?php echo htmlspecialchars($admin["username"]); ?>)</option>
                                <?php endforeach; ?>  
                            <!-- <option value="open">Open</option>
                                <option value="in_progress">In Progress</option>
                                <option value="closed">Closed</option> -->

                            </select>
                            <!-- <input type="text" name="assigned_to" id="assigned-to-update" class="form-control" placeholder="Enter assignee name"> -->
                        </div>

                        <button type="submit" class="btn btn-primary">Update Ticket</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
