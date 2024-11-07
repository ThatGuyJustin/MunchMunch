<?php
require_once 'util.php';
require_once 'nav.php';

start_session();

$error_message = '';
$success_message = '';

// Fetch all ticket requests
$tickets = api_request_with_token("api/admin/requests", "GET");

// Handle form submission for updating tickets
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ticket_id = $_POST['ticket_id'];
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
        if (isset($update_response['success']) && $update_response['success'] === true) {
            $success_message = "Ticket updated successfully!";
        } else {
            $error_message = "Failed to update ticket.";
        }
    }

    // Post a new response message to the ticket
    if ($response_message) {
        $response_data = ['message' => $response_message];
        $message_response = api_request_with_token("api/admin/requests/$ticket_id/messages", "POST", $response_data);
        if (isset($message_response['success']) && $message_response['success'] === true) {
            $success_message = "Response posted successfully!";
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
    <title>Admin Dashboard - Ticket Management</title>
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

        <?php if ($tickets && isset($tickets['data']) && count($tickets['data']) > 0): ?>
            <div class="accordion" id="ticketAccordion">
                <?php foreach ($tickets['data'] as $ticket): ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading-<?php echo $ticket['id']; ?>">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?php echo $ticket['id']; ?>" aria-expanded="true" aria-controls="collapse-<?php echo $ticket['id']; ?>">
                                <?php echo htmlspecialchars($ticket['subject']); ?> - Status: <?php echo htmlspecialchars($ticket['status']); ?>
                            </button>
                        </h2>
                        <div id="collapse-<?php echo $ticket['id']; ?>" class="accordion-collapse collapse" aria-labelledby="heading-<?php echo $ticket['id']; ?>" data-bs-parent="#ticketAccordion">
                            <div class="accordion-body">
                                <p><strong>Submitted by:</strong> <?php echo htmlspecialchars($ticket['user_name']); ?></p>
                                <p><strong>Message:</strong> <?php echo htmlspecialchars($ticket['message']); ?></p>
                                <p><strong>Assigned to:</strong> <?php echo htmlspecialchars($ticket['assigned_to'] ?? 'Not assigned'); ?></p>

                                <!-- Display existing responses -->
                                <?php if (!empty($ticket['responses'])): ?>
                                    <div class="responses mt-3">
                                        <h6>Responses:</h6>
                                        <?php foreach ($ticket['responses'] as $response): ?>
                                            <p><strong><?php echo htmlspecialchars($response['author']); ?>:</strong> <?php echo htmlspecialchars($response['message']); ?></p>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <!-- Admin form to update ticket -->
                                <form action="admin_dashboard.php" method="post" class="mt-3">
                                    <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">

                                    <div class="mb-3">
                                        <label for="status-<?php echo $ticket['id']; ?>" class="form-label">Update Status:</label>
                                        <select name="status" id="status-<?php echo $ticket['id']; ?>" class="form-select">
                                            <option value="open" <?php echo $ticket['status'] == 'open' ? 'selected' : ''; ?>>Open</option>
                                            <option value="in_progress" <?php echo $ticket['status'] == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                            <option value="closed" <?php echo $ticket['status'] == 'closed' ? 'selected' : ''; ?>>Closed</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="assigned_to-<?php echo $ticket['id']; ?>" class="form-label">Assign to:</label>
                                        <input type="text" name="assigned_to" id="assigned_to-<?php echo $ticket['id']; ?>" class="form-control" placeholder="Enter assignee name">
                                    </div>

                                    <div class="mb-3">
                                        <label for="response_message-<?php echo $ticket['id']; ?>" class="form-label">Post a Response:</label>
                                        <textarea name="response_message" id="response_message-<?php echo $ticket['id']; ?>" class="form-control" rows="3"></textarea>
                                    </div>

                                    <button type="submit" class="btn btn-primary">Update Ticket</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No tickets found.</p>
        <?php endif; ?>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
