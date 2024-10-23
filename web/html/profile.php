<?php
require_once 'util.php'; // Include utility functions
require_once 'nav.php';
session_start();

$is_error = false;
// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$profile = "self";

if(!$_GET["user"]){
    $profile = "self";
}else{
    $profile = $_GET["user"];
}

$api_path_profile = "api/users/$profile";
$response = api_request_with_token($api_path_profile);

$uploaded_recipes = [];

$favorited_recipes = [];

$user = null;
if ($response['code'] != 200){
    $user = array(
        "username" => "",
        "name" => "User Not Found",
        "id" => 0
    );
    $is_error = true;
}else{
    $user = $response["data"];
}
$api_path_recipes = "api/users/" . $user["id"] . "/recipes";
$api_path_favorites = "api/users/" . $user["id"] . "/favorites";

if (!$is_error){
    if($_SESSION['user_id'] != $user["id"]){
        if(in_array("PRIVATE_PROFILE", $user["account_flags"])){
            $uploaded_recipes = [
                ['title' => "This Account is private.", 'created_at' => '']
            ];
            $favorited_recipes = [
                ['title' => "This Account is private.", 'created_at' => '']
            ];
        }else{
            $recipe_response = api_request_with_token($api_path_recipes);
            $uploaded_recipes = $recipe_response['data'];
        }
        if(in_array("PRIVATE_FAVORITES", $user["account_flags"])){
            $favorited_recipes = [
                ['title' => "This Account is not sharing their favorites.", 'created_at' => '']
            ];
        }else{
            $fav_response = api_request_with_token($api_path_favorites);
            $favorited_recipes = $fav_response['data'];
        }
    }

    // $uploaded_recipes = [
    //     ['title' => 'Spaghetti Carbonara', 'date' => '2024-09-28'],
    //     ['title' => 'Chicken Curry', 'date' => '2024-10-02'],
    // ];
    // $favorited_recipes = [
    //     ['title' => 'Vegan Brownies', 'date' => '2024-09-10'],
    //     ['title' => 'Tacos al Pastor', 'date' => '2024-09-22'],
    // ];
}


// Define the profile image URL (This can be dynamically generated based on the user ID and stored avatar)
$profile_image_url = "/api/media/avatars/" . $user['id'] . "/" . "avatar.png";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php echo($NAV_HEADERS) ?>
    <meta charset="UTF-8">
    <title>Profile - <?php echo($user["name"] . " (" . $user["username"] . ")"); ?></title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .profile-img {
            width: 150px;
            height: 150px;
            object-fit: cover;
        }
        .profile-box {
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 10px;
            background-color: #f8f9fa;
        }
        .tab-content {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <?php echo($NAV_ICONS) ?>
    <?php if($is_error) echo("<div class='alert alert-danger' role='alert'><center>This user does not exist.</center></div>"); ?>
    <div class="container mt-5">
        <div class="row">
            <!-- Left Side - Profile Section (Now in a bordered box) -->
            <div class="col-md-4">
                <div class="profile-box">
                    <div class="text-center mb-4">
                        <img src="<?php echo $profile_image_url; ?>" <?php if($is_error) echo("style='filter: grayscale(1);'"); ?>class="rounded-circle profile-img mb-3" alt="Profile Image">
                        <h4 class="card-title"><?php echo htmlspecialchars($user['name']); ?></h4>
                        <p class="text-muted">@<?php echo htmlspecialchars($user['username']); ?></p>
                        <!-- <p><strong>Dietary Preferences:</strong> <?php echo htmlspecialchars($user['preferences']); ?></p> -->
                    </div>
                </div>
            </div>

            <!-- Right Side - Recipes Section -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <!-- Bootstrap Nav Tabs -->
                        <ul class="nav nav-tabs card-header-tabs" id="recipeTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="uploaded-tab" data-bs-toggle="tab" data-bs-target="#uploaded" type="button" role="tab" aria-controls="uploaded" aria-selected="true">Recipes Uploaded</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="favorited-tab" data-bs-toggle="tab" data-bs-target="#favorited" type="button" role="tab" aria-controls="favorited" aria-selected="false">Recipes Favorited</button>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <!-- Tab Content -->
                        <div class="tab-content" id="recipeTabContent">
                            <!-- Recipes Uploaded Tab -->
                            <div class="tab-pane fade show active" id="uploaded" role="tabpanel" aria-labelledby="uploaded-tab">
                                <ul class="list-group list-group-flush">
                                    <?php if (!empty($uploaded_recipes)): ?>
                                        <?php foreach ($uploaded_recipes as $recipe): ?>
                                            <li class="list-group-item">
                                                <strong><a href="/card.php?recipe=<?php echo htmlspecialchars($recipe['id']); ?>"><?php echo htmlspecialchars($recipe['title']); ?></a></strong> 
                                                <span class="text-muted"><?php if($recipe['created_at'] != '') echo("(Uploaded on " . htmlspecialchars($recipe['created_at'] . ")")); ?></span>
                                            </li>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <li class="list-group-item">No recipes uploaded yet.</li>
                                    <?php endif; ?>
                                </ul>
                            </div>

                            <!-- Recipes Favorited Tab -->
                            <div class="tab-pane fade" id="favorited" role="tabpanel" aria-labelledby="favorited-tab">
                                <ul class="list-group list-group-flush">
                                    <?php if (!empty($favorited_recipes)): ?>
                                        <?php foreach ($favorited_recipes as $recipe): ?>
                                            <li class="list-group-item">
                                                <strong><?php echo htmlspecialchars($recipe['title']); ?></strong> 
                                                <!-- <span class="text-muted"><?php if($recipe['created_at'] != '') echo("(Favorited on " . htmlspecialchars($recipe['created_at'] . ")")); ?></span> -->
                                            </li>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <li class="list-group-item">No recipes favorited yet.</li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
