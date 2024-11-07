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
    $preferences = isset($_POST['preferences']) ? $_POST['preferences'] : [];
    // $password = trim($_POST['password']);
    $profile_image = $_FILES['profile_image'];
    $account_flags = $user['account_flags'];

    if($_POST['private_profile'] && !in_array("PRIVATE_PROFILE", $account_flags)){
        // Add to account_flags
        array_push($account_flags, "PRIVATE_PROFILE");
    }
    if(!$_POST['private_profile'] && in_array("PRIVATE_PROFILE", $account_flags)){
        // Remove from account_flags
        $account_flags = array_diff($account_flags, ["PRIVATE_PROFILE"]);
    }

    if($_POST['private_favorites'] && !in_array("PRIVATE_FAVORITES", $account_flags)){
        // Add to account_flags
        array_push($account_flags, "PRIVATE_FAVORITES");
    }
    if(!$_POST['private_favorites'] && in_array("PRIVATE_FAVORITES", $account_flags)){
        // Remove from account_flags
        $account_flags = array_diff($account_flags, ["PRIVATE_FAVORITES"]);
    }

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
            'preferences' => ["dietary" => $preferences],
            'account_flags' => $account_flags,
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

$ID_TO_NAME = array(
    "vegetarian" => "Vegetarian",
    "vegan" => "Vegan",
    "gluten_free" => "Gluten-Free",
    "paleo" => "Paleo",
    "keto" => "Keto",
    "dairy_free" => "Dairy-Free",
    "lactose_intolerance" => "Lactose Intolerance",
    "halal" => "Halal",
    "low_carb" => "Low Carb",
);

function checkDisplay($type){
    if(in_array($type, $user["preferences"]["dietary"])){
        echo htmlspecialchars("none");
    }else{
        echo htmlspecialchars("block");
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
        <div class="container">
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
                    <div class="col-sm-10 item-box">
                        <div class="col col-sm-2 mb-3 me-2">
                            <select id="preferences" class="form-select" onchange="addItem()">
                                <option value="" disabled selected>Select Preferences</option>
                                <option style="display: <?php if(in_array("vegetarian", $user["preferences"]["dietary"])) echo "none"; else "block"; ?>" value="vegetarian">Vegetarian</option>
                                <option style="display: <?php if(in_array("vegan", $user["preferences"]["dietary"])) echo "none"; else "block"; ?>" value="vegan">Vegan</option>
                                <option style="display: <?php if(in_array("gluten_free", $user["preferences"]["dietary"])) echo "none"; else "block"; ?>" value="gluten_free">Gluten-Free</option>
                                <option style="display: <?php if(in_array("paleo", $user["preferences"]["dietary"])) echo "none"; else "block"; ?>" value="paleo">Paleo</option>
                                <option style="display: <?php if(in_array("keto", $user["preferences"]["dietary"])) echo "none"; else "block"; ?>" value="keto">Keto</option>
                                <option style="display: <?php if(in_array("dairy_free", $user["preferences"]["dietary"])) echo "none"; else "block"; ?>" value="dairy_free">Dairy-Free</option>
                                <option style="display: <?php if(in_array("lactose_intolerance", $user["preferences"]["dietary"])) echo "none"; else "block"; ?>" value="lactose_intolerance">Lactose Intolerance</option>
                                <option style="display: <?php if(in_array("halal", $user["preferences"]["dietary"])) echo "none"; else "block"; ?>" value="halal">Halal</option>
                                <option style="display: <?php if(in_array("low_carb", $user["preferences"]["dietary"])) echo "none"; else "block"; ?>" value="low_carb">Low Carb</option>
                            </select>
                        </div>
                        <div class="col-lg-auto" id="selectedPreferences">
                            <?php if (!empty($user["preferences"]["dietary"])): ?>
                                <?php foreach ($user["preferences"]["dietary"] as $dpref): ?>
                                    <div class="item">
                                        <?php echo htmlspecialchars($ID_TO_NAME[$dpref]);?>
                                        <i id="item-<?php echo htmlspecialchars($dpref);?>" data="<?php echo htmlspecialchars($dpref);?>" class="remove fa fa-times-circle"></i>
                                        <input type="hidden" name="preferences[]" value="<?php echo htmlspecialchars($dpref); ?>" id="input-<?php echo htmlspecialchars($dpref); ?>">
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-1 col-form-label" for="privacy">Privacy</label>
                    <div class="form-check form-check-inline col-sm-2">
                        <input class="form-check-input" type="checkbox" name="private_profile" id="private_profile" value="true" <?php if(in_array("PRIVATE_PROFILE", $user['account_flags'])){echo "checked";}?>>
                        <label class="form-check-label" for="private_profile">Private Profile</label>
                    </div>
                    <div class="form-check form-check-inline col-sm-2">
                        <input class="form-check-input" type="checkbox" name="private_favorites" id="private_favorites" value="true" <?php if(in_array("PRIVATE_FAVORITES", $user['account_flags'])){echo "checked";}?>>
                        <label class="form-check-label" for="private_favorites">Private Favorites</label>
                    </div>
                    <!-- <label class="col-sm-1 col-form-label" for="password">New Password</label>
                    <div class="col-sm-10">
                        <input class="form-control" type="password" name="password" id="password">
                    </div> -->
                </div>
                <div class="row mb-3">
                    <label class="col-sm-1 col-form-label" for="profile_image">Profile Image</label>
                    <div class="col-sm-10">
                        <input class="form-control" type="file" name="profile_image" id="profile_image">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-2">
                        <input class="btn btn-outline-success btn-lg" type="submit" value="Update Profile">
                    </div>
                    <div class="col-sm-3">
                        <a class="btn btn-primary btn-lg" href="dashboard.php">Back to Dashboard</a>
                    </div>
                </div>
            </form>
        </div>
    </body>
    <script>
        function addItem() {
            const select = document.getElementById('preferences');
            const selectedValue = select.value;
            const selectedLabel = select.options[select.selectedIndex].text;

            if (selectedValue) {
                const selectedItemsDiv = document.getElementById('selectedPreferences');

                // Create a new item element
                const itemDiv = document.createElement('div');
                itemDiv.className = 'item';
                itemDiv.textContent = selectedLabel;

                // Create the remove button
                const removeBtn = document.createElement('i');
                removeBtn.className = 'remove fa fa-times-circle';
                removeBtn.id = "item";
                removeBtn.data = select.value;
                removeBtn.onclick = function() {
                    selectedItemsDiv.removeChild(itemDiv);
                    const option = Array.from(select.options).find(opt => opt.value === selectedValue);
                    if (option) {
                        option.style.display = 'block'; // Re-show the option in the select
                    }

                    // // Remove the hidden input
                    // const hiddenInput = document.getElementById(`input-${selectedValue}`);
                    // if (hiddenInput) {
                    //     hiddenInput.parentNode.removeChild(hiddenInput);
                    // }
                };

                itemDiv.appendChild(removeBtn);
                selectedItemsDiv.appendChild(itemDiv);

                // Create hidden input for the selected item
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'preferences[]';
                hiddenInput.value = selectedValue;
                hiddenInput.id = `input-${selectedValue}`;
                removeBtn.appendChild(hiddenInput);

                // Remove the selected option from the select menu
                const option = Array.from(select.options).find(opt => opt.value === selectedValue);
                if (option) {
                    option.style.display = 'none'; // Hide the option in the select
                }

                // Reset the select menu
                select.selectedIndex = 0;
            }
        }
    </script>
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script>
            $("[id^=item]").on("click",  function(event) {
                let YEET = event.target.getAttribute("data");
                console.log(YEET);
                const selectedItemsDiv = document.getElementById('selectedPreferences');
                const select = document.getElementById('preferences');
                console.log(event.target.parentElement);
                selectedItemsDiv.removeChild(event.target.parentElement);
                const option = Array.from(select.options).find(opt => opt.value === YEET);
                if (option) {
                    option.style.display = 'block';
                }
            });
    </script>
</html>
