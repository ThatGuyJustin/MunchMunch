<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch current user profile data from the backend API
$api_url = 'http://backend:5000/api/get_profile?user_id=' . $user_id;
$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);

if ($response === false) {
    die('Error communicating with the backend server.');
}

$user = json_decode($response, true);

curl_close($ch);

// Handle form submission for profile updates, including image upload
$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and retrieve the input values
    $email = trim($_POST['email']);
    $preferences = trim($_POST['preferences']);
    $password = trim($_POST['password']);
    $profile_image = $_FILES['profile_image'];

    // Handle file upload
    if (!empty($profile_image['name'])) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($profile_image['name']);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        // Validate file type (allowing only image formats)
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($imageFileType, $allowed_types)) {
            $error_message = 'Only JPG, JPEG, PNG, and GIF files are allowed.';
        } elseif ($profile_image['size'] > 5000000) { // File size limit (5MB)
            $error_message = 'File is too large.';
        } else {
            // Move uploaded file to the server's target directory
            if (move_uploaded_file($profile_image['tmp_name'], $target_file)) {
                // Update profile image path in the backend
                $profile_image_url = $target_file;
            } else {
                $error_message = 'There was an error uploading your profile image.';
            }
        }
    }

    if (empty($error_message)) {
        // Prepare the data for API request
        $data = [
            'user_id' => $user_id,
            'email' => $email,
            'preferences' => $preferences,
            'password' => $password,
            'profile_image' => isset($profile_image_url) ? $profile_image_url : $user['profile_image'], // Keep old image if not changed
        ];

        // Send update request to the backend API
        $api_url = 'http://backend:5000/api/update_profile';
        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $response = curl_exec($ch);

        if ($response === false) {
            $error_message = 'Error communicating with the backend server.';
        } else {
            $result = json_decode($response, true);
            if (isset($result['code']) && $result['code'] === 200) {
                $success_message = 'Profile updated successfully!';
                // Update session with the new profile image if changed
                $_SESSION['profile_image'] = $profile_image_url;
            } else {
                $error_message = isset($result['msg']) ? $result['msg'] : 'Profile update failed.';
            }
        }

        curl_close($ch);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Profile - FoodTinder</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <h1>Update Profile</h1>

    <!-- Display success or error messages -->
    <?php if (!empty($error_message)): ?>
        <p class="error"><?php echo htmlspecialchars($error_message); ?></p>
    <?php elseif (!empty($success_message)): ?>
        <p class="success"><?php echo htmlspecialchars($success_message); ?></p>
    <?php endif; ?>

    <!-- Display current profile picture -->
    <div>
        <h3>Current Profile Picture:</h3>
        <img src="<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile Image" width="150">
    </div>

    <form action="profile.php" method="post" enctype="multipart/form-data">
        <label for="email">Email:</label>
        <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" required><br>

        <label for="preferences">Dietary Preferences:</label>
        <input type="text" name="preferences" id="preferences" value="<?php echo htmlspecialchars($user['preferences']); ?>"><br>

        <label for="password">New Password (optional):</label>
        <input type="password" name="password" id="password"><br>

        <label for="profile_image">Profile Image (optional):</label>
        <input type="file" name="profile_image" id="profile_image"><br>

        <input type="submit" value="Update Profile">
    </form>

    <p><a href="dashboard.php">Back to Dashboard</a></p>
</body>
</html>
