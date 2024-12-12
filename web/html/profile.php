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

if(!isset($_GET["user"])){
    $profile = "self";
}else{
    $profile = $_GET["user"];
}

$api_path_profile = "api/users/$profile";
$response = api_request_with_token($api_path_profile);

$uploaded_recipes = [];

$favorited_recipes = [];

$viewed_recipes = [];

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
    }else{
        $viewed_recipes = api_request_with_token("api/users/" . $user["id"] . "/history")["data"];
        $recipe_response = api_request_with_token($api_path_recipes);
        $uploaded_recipes = $recipe_response['data'];
        $fav_response = api_request_with_token($api_path_favorites);
        $favorited_recipes = $fav_response['data'];
    }
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
                            <?php if($_SESSION["user_id"] == $user["id"]): ?>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="favorited-tab" data-bs-toggle="tab" data-bs-target="#history" type="button" role="tab" aria-controls="history" aria-selected="false">History</button>
                                </li>
                            <?php endif; ?>
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
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong><a href="/recipe.php?id=<?php echo htmlspecialchars($recipe['id']); ?>"><?php echo htmlspecialchars($recipe['title']); ?></a></strong> 
                                                    <span class="text-muted"><?php if($recipe['created_at'] != '') echo("(Uploaded on " . htmlspecialchars($recipe['created_at'] . ")")); ?></span>
                                                </div>
                                                <!-- Shopping Cart Button -->
                                                <form action ="shopping_list.php?id=<?php echo htmlspecialchars($recipe['id']);?>" method = "post">
                                                <button class="btn btn-outline-primary btn-sm" type="submit" name="add_to_shopping_list">
                                                    <i class="fas fa-shopping-cart"></i>
                                                </button>
                                        </form>
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
                                    <?php if (!empty($favorited_recipes["favorites"])): ?>
                                        <?php foreach ($favorited_recipes["favorites"] as $recipe): ?>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong><a href="/recipe.php?id=<?php echo htmlspecialchars($recipe); ?>"><?php echo htmlspecialchars($favorited_recipes["raw_recipes"][$recipe]['title']); ?></strong> 
                                                    <span class="text-muted"><?php if($favorited_recipes["raw_recipes"][$recipe]['created_at'] != '') echo("(Favorited on " . htmlspecialchars($favorited_recipes["raw_recipes"][$recipe]['created_at'] . ")")); ?></span>
                                                </div>
                                                <form action="shopping_list.php?id=<?php echo htmlspecialchars($recipe); ?>" method="post">
                                                    <button class="btn btn-outline-primary btn-sm" type="submit" name="add_to_shopping_list" data-bs-toggle="tooltip" title="Add to your shopping list">
                                                        <i class="fas fa-shopping-cart"></i>
                                                    </button>
                                                </form>
                                            </li>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <li class="list-group-item">No recipes favorited yet.</li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                            <!-- Viewed Recipes (History) -->
                            <?php if ($_SESSION["user_id"] == $user["id"]): ?>
                                <div class="tab-pane fade" id="history" role="tabpanel" aria-labelledby="history-tab">
                                    <ul class="list-group list-group-flush">
                                        <?php if (!empty($viewed_recipes["history"])): ?>
                                            <?php foreach ($viewed_recipes["history"] as $recipe): ?>
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <strong><a href="/recipe.php?id=<?php echo htmlspecialchars($recipe['recipe']); ?>"><?php echo htmlspecialchars($viewed_recipes["raw_recipes"][$recipe['recipe']]['title']); ?></a></strong>
                                                        <span class="text-muted">(Viewed on <?php echo htmlspecialchars($recipe['timestamp']); ?>)</span>
                                                    </div>
                                                    <!-- Add to Shopping Cart Button -->
                                                    <form action="shopping_list.php?id=<?php echo htmlspecialchars($recipe['recipe']); ?>" method="post">
                                                        <button class="btn btn-outline-primary btn-sm" type="submit" name="add_to_shopping_list" data-bs-toggle="tooltip" title="Add to your shopping list">
                                                            <i class="fas fa-shopping-cart"></i>
                                                        </button>
                                                    </form>
                                                </li>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <li class="list-group-item">No recently viewed recipes.</li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
