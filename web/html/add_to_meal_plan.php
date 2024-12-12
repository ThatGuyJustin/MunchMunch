<?php
require_once 'util.php'; // Include utility functions
require_once 'nav.php';
start_session();

if (!is_user_logged_in()) {
    header('Location: login.php');
    exit();
}

// Days of the week
$days_of_week = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"];

// Retrieve user ID from session
$user_id = $_SESSION['user_id'];

$api_path_meal_plan = "api/users/$user_id/meal-plan"; 
$meal_plan_response = api_request_with_token($api_path_meal_plan);
$meal_plan_data = $meal_plan_response['data']['plan'];

$display = $meal_plan_data;

// Handle adding a recipe to a meal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_recipe']))
{
    $new_recipe_id = $_GET['id'];
    $t = $_POST['meal_name']; 
    $day = $_POST['day'];
    
    $api_path_recipe = "api/recipes/$new_recipe_id";
    $recipe_response = api_request_with_token($api_path_recipe);
    if (isset($recipe_response['code']) && $recipe_response['code'] === 200) 
    {
        $recipe_data = $recipe_response['data'];
        $recipe_name = $recipe_data['title'];
        $pushed = "$recipe_name|$new_recipe_id";

        array_push($meal_plan_data[$day][$t], $pushed);

        $meal_plan_response['data']['plan'] = $meal_plan_data;

        // Prepare the data for the API request
        $payload = [
            'plan' => $meal_plan_response['data']['plan'],
        ];

        $update_response = api_request_with_token("api/users/$user_id/meal-plan", 'PATCH', $payload);

        // Check for a successful response
        if (isset($update_response['code']) && $update_response['code'] === 200) {
            header("Location: meal_plan.php");
            exit();
        } else {
            $error_message = "Failed to update meal plan.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php echo($NAV_HEADERS) ?>
    <meta charset="UTF-8">
    <title>Meal Plan</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .meal-plan-box {
            border: 1px solid #ddd;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 10px;
            background-color: #f8f9fa;
        }
        .meal-card {
            margin-bottom: 15px;
        }
        .meal-box {
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .meal-box:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        .nav-tabs {
            margin-bottom: 20px;
        }
        .container {
            max-width: 900px;
        }
        .btn {
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <?php echo($NAV_ICONS) ?>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Your Weekly Meal Plan</h1>

        <?php if (!empty($success_message)): ?>
            <div class="message success-message"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
            <div class="message error-message"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <!-- Bootstrap Nav Tabs for Days of the Week -->
        <ul class="nav nav-tabs justify-content-center" id="mealPlanTabs" role="tablist">
            <?php foreach ($days_of_week as $index => $day): ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?php echo $index == 0 ? 'active' : ''; ?>" id="<?php echo strtolower($day); ?>-tab" data-bs-toggle="tab" data-bs-target="#<?php echo strtolower($day); ?>" type="button" role="tab" aria-controls="<?php echo strtolower($day); ?>" aria-selected="<?php echo $index == 0 ? 'true' : 'false'; ?>">
                        <?php echo $day; ?>
                    </button>
                </li>
            <?php endforeach; ?>
        </ul>

        <div class="tab-content mt-4" id="mealPlanTabContent">
            <?php foreach ($days_of_week as $index => $day): ?>
                <div class="tab-pane fade <?php echo $index == 0 ? 'show active' : ''; ?>" id="<?php echo strtolower($day); ?>" role="tabpanel" aria-labelledby="<?php echo strtolower($day); ?>-tab">
                    <div class="meal-plan-box">
                        <h5 class="text-center"><?php echo $day; ?> Meals</h5>

                        <?php if (!empty($display[$day])): ?>
                            <?php foreach ($display[$day] as $meal_name => $recipes): ?>
                                <div class="meal-box">
                                    <h6><?php echo htmlspecialchars($meal_name); ?></h6>
                                    <?php if (!empty($recipes)): ?>
                                        <ul>
                                            <?php foreach ($recipes as $recipe): ?>
                                                <li><?php echo htmlspecialchars(explode("|", $recipe)[0]); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php else: ?>
                                        <p>No recipes added for this meal yet.</p>
                                    <?php endif; ?>

                                    <!-- Add Recipe Button -->
                                    <form method="POST" action="">
                                        <input type="hidden" name="day" value="<?php echo htmlspecialchars($day); ?>">
                                        <input type="hidden" name="meal_name" value="<?php echo htmlspecialchars($meal_name); ?>">
                                        <input type="hidden" name="recipe_id" value="<?php echo htmlspecialchars($recipe_id_from_previous_page); ?>">
                                        <button type="submit" name="add_recipe" class="btn btn-secondary btn-sm">Add Recipe</button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No meals planned for <?php echo htmlspecialchars($day); ?> yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>