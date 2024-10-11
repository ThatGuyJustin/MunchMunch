<?php
require_once 'util.php'; // Include utility functions
require_once 'nav.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch current user profile data from the backend API
$api_path = 'api/users/self';
$response = api_request_with_token($api_path);
$user = $response["data"];

// Handle form submission for profile updates, including image upload
$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and retrieve the input values
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $preferences = trim($_POST['preferences']);
    // $password = trim($_POST['password']);
    $profile_image = $_FILES['profile_image'];

    // Handle file upload
    if (!empty($profile_image['name'])) {
        // Validate file type (allowing only image formats)
        // $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        // if (!in_array($imageFileType, $allowed_types)) {
        //     $error_message = 'Only JPG, JPEG, PNG, and GIF files are allowed.';
        // } else
        if ($profile_image['size'] > 50000000) { // File size limit (5MB)
            $error_message = 'File is too large.';
        } else {
            $updoot_image = api_request_with_token("api/media/avatars", "POST", null, $profile_image);
            // // Move uploaded file to the server's target directory
            // if (move_uploaded_file($profile_image['tmp_name'], $target_file)) {
            //     // Update profile image path in the backend
            //     $profile_image_url = $target_file;
            // } else {
            //     $error_message = 'There was an error uploading your profile image.';
            // }
        }
    }

    if (empty($error_message)) {
        // Prepare the data for API request
        $data = [
            'name' => $name,
            'email' => $email,
            'preferences' => $preferences,
            // 'password' => $password,
            // 'avatar' => isset($profile_image_url) ? $profile_image_url : $user['avatar'], // Keep old image if not changed
        ];

        // Send update request to the backend API
        $api_url = 'api/users/' . $user_id;
        $response = api_request_with_token($api_url, "PATCH", $data);

        if (isset($response['code']) && $response['code'] === 200) {
            $success_message = 'Profile updated successfully!';
            $user = $response['data'];
            // Update session with the new profile image if changed
            $_SESSION['profile_image'] = $profile_image_url;
        } else {
            $error_message = isset($response['msg']) ? $response['msg'] : 'Profile update failed.';
        }

    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php echo($NAV_HEADERS) ?>
    <meta charset="UTF-8">
    <title>Update Profile - FoodTinder</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/bootstrap.css">
</head>
<body>
    <?php echo($NAV_ICONS) ?>
    <center><h1>Update Account</h1></center>
    <!-- Display success or error messages -->
    <?php if (!empty($error_message)): ?>
        <div class="container">
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        </div>
    <?php elseif (!empty($success_message)): ?>
        <div class="container">
            <div class="alert alert-success" role="alert">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Display current profile picture -->
    <!-- <div class=container>
        <img class="img-thumbnail rounded" src="<?php echo $_SESSION["HTTP_HOST"] . "/api/media/avatars/" . $_SESSION['user_id'] . "/" . htmlspecialchars($user['avatar']); ?>" width="100" height="100" alt="Profile Image" width="150">
    </div> -->
    <div class=container>
        <img class="img-thumbnail rounded mx-auto d-block row mb-3" src="<?php echo $_SESSION["HTTP_HOST"] . "/api/media/avatars/" . $_SESSION['user_id'] . "/" . htmlspecialchars($user['avatar']); ?>" width="100" height="100" alt="Profile Image" width="150">
        <form action="account.php" method="post" enctype="multipart/form-data">
            <div class="row mb-3">
                <label for="username" class="col-sm-1 col-form-label">Username</label>
                <div class="col-sm-10">
                    <input class="form-control" type="text" name="username" id="username" value="<?php echo htmlspecialchars($user['username']);?>" disabled>
                </div>
            </div>
            <div class="row mb-3">
              <label class="col-sm-1 col-form-label" for="name">Name</label>
                <div class="col-sm-10">
                    <input class="form-control" type="text" name="name" id="name" value="<?php echo htmlspecialchars($user['name']); ?>">
                </div>
            </div>
            <div class="row mb-3">
                <label class="col-sm-1 col-form-label" for="email">Email</label>
                <div class="col-sm-10">
                    <input class="form-control" type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
            </div>
            <div class="row mb-3">  
                <label class="col-sm-1 col-form-label" for="preferences">Dietary Preferences</label>
                <div class="col-sm-10">
                    <input class="form-control" type="text" name="preferences" id="preferences" value="<?php echo htmlspecialchars($user['preferences']); ?>">
                </div>
            </div>
            <div class="row mb-3">
                <label class="col-sm-1 col-form-label" for="password">New Password</label>
                <div class="col-sm-10">
                    <input class="form-control" type="password" name="password" id="password">
                </div>
            <div class="row mb-3">
                <label class="col-sm-1 col-form-label" for="profile_image">Profile Image</label>
                <div class="col-sm-10">
                    <input class="form-control" type="file" name="profile_image" id="profile_image">
                </div>
            </div>
            <div class="row mb-3">
                <input type="submit" value="Update Profile">
            </div>
        </form>
    </div>

    <p><a href="dashboard.php">Back to Dashboard</a></p>
</body>
</html>
