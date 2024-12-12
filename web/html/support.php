<?php
require_once 'util.php';
require_once 'nav.php';

start_session();

$error_message = '';
$success_message = '';

// Check if admin or user
$user_id = $_SESSION['user_id'] ?? null;

$user = api_request_with_token("api/users/self", "GET")["data"];
$is_admin = in_array("ADMIN", $user["account_flags"]);

// based on role adjust
$api_endpoint = "api/admin/requests";
$tickets = api_request_with_token($api_endpoint, "GET");

// Check if tickets were retrieved
if (!$tickets || !isset($tickets['data']) || count($tickets['data']) === 0) {
    $error_message = "No tickets found or failed to fetch tickets.";
}

// Get the current ticket ID 
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
    <title>User Dashboard - Ticket Management</title>
    <script>
        function showTicket(ticketId) {
            const ticketDetails = document.getElementsByClassName('ticket-details');
            for (let i = 0; i < ticketDetails.length; i++) {
                ticketDetails[i].style.display = 'none';
            }
            document.getElementById('ticket-' + ticketId).style.display = 'block';
            document.getElementById('ticket-id-update').value = ticketId;
        }
        
        // Function to open the ticket passed 
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
                                <?php echo htmlspecialchars("[" . $ticket["id"] . "] " . $ticket['subject']); ?>
                                <?php if($is_admin): ?>
                                    <br><i class="fa fa-user" aria-hidden="true"></i><?php echo htmlspecialchars(" " . $ticket['user']['username']); ?>
                                <?php endif; ?>
                                <br><small>Status: <?php echo htmlspecialchars($ticket['status']); ?></small>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No tickets found.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Main View: Ticket Details -->
            <?php if ($is_admin): ?>
                <div class="col-md-6">
            <?php else: ?>
                <div class="col-md-9">
            <?php endif; ?>
                <?php if ($tickets && isset($tickets['data']) && count($tickets['data']) > 0): ?>
                    <?php foreach ($tickets['data'] as $ticket): ?>
                        <div id="ticket-<?php echo $ticket['id']; ?>" class="ticket-details" style="display: none;">
                            <h4><?php echo htmlspecialchars($ticket['subject']); ?></h4>
                            <p><strong>Status:</strong> <?php echo htmlspecialchars($ticket['status']); ?></p>
                            <hr>

                            <!-- Display list of message objects -->
                            <?php if (!empty($ticket['messages'])): ?>
                                <div class="responses mt-3">
                                    <!-- <h6>Messages:</h6> -->
                                    <?php foreach ($ticket['messages'] as $message): ?>
                                        <div class="container d-flex align-items-center gap-1" style="display: flex; align-items: center; padding-bottom:1em;">
                                            <img style="width: 32px; height: 32px;" src="<?php echo "/api/media/avatars/" . $message['user']['id'] . "/" . "avatar.png"; ?>" class="rounded-circle profile-img" alt="Profile Image">
                                            
                                            <strong><?php echo htmlspecialchars($message['user']['username']); ?></strong>
                                            
                                            <?php if (in_array("ADMIN", $message['user']["account_flags"])): ?>
                                                <i class="fa fa-star text-warning" aria-hidden="true"></i>
                                            <?php endif; ?>
                                            
                                            <small class="ms-auto"><?php echo htmlspecialchars(date('Y-m-d H:i:s', $message['timestamp'])); ?></small>
                                        </div>
                                        <div>
                                            <p style="padding-left: 5%;"><?php echo htmlspecialchars($message['message']); ?></p>
                                        </div>
                                        <hr>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <!-- Form to post a response message -->
                            <form action="support.php" method="post" class="mt-3">
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
            <?php if ($is_admin): ?>
                <!-- Update ticket section on the right -->
                <div class="col-md-3">
                    <div class="update-ticket-section mt-3">
                        <h5>Update Ticket</h5>
                        <form action="support.php" method="post">
                            <input type="hidden" name="ticket_id" id="ticket-id-update" value="">

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
                                <option value="null"></option>
                                <?php foreach (api_request_with_token("api/search?type=admins")["data"] as $admin): ?>
                                    <option value="<?php echo htmlspecialchars($admin["id"]); ?>">
                                        <?php echo htmlspecialchars($admin["username"]); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                            <button type="submit" class="btn btn-primary">Update Ticket</button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>
