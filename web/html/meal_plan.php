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

// Fetch user's meal plans using the API
$api_path_meal_plan = "api/users/$user_id/meal-plan"; 
$meal_plan_response = api_request_with_token($api_path_meal_plan);

$display = $meal_plan_response['data']['plan'];

// Handle form submission for adding or deleting a meal/recipe
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_meal'])) {
        $selected_day = $_POST['day'];
        $meal_name = $_POST['meal_name'];

        

        $meal_plan_data = $meal_plan_response['data']['plan'] ?? [
            "Monday" => [], "Tuesday" => [], "Wednesday" => [], "Thursday" => [], "Friday" => [], "Saturday" => [], "Sunday" => []
        ];

        $meal_plan_data[$selected_day][$meal_name] = [];
        $meal_plan_response['data']['plan'] = $meal_plan_data;

        // Prepare the data for the API request
        $payload = ['plan' => $meal_plan_response['data']['plan']];

        // Send the updated meal plan to the API
        $update_response = api_request_with_token("api/users/$user_id/meal-plan", 'PATCH', $payload);

        if (isset($update_response['code']) && $update_response['code'] === 200) {
            header("Location: meal_plan.php");
            exit();
        } else {
            $error_message = "Failed to update meal plan.";
        }
    }

    if (isset($_POST['delete_meal'])) {
        $day_to_delete = $_POST['day_to_delete'];
        $meal_name_to_delete = $_POST['meal_name_to_delete'];
        $recipe_name_to_delete = $_POST['recipe_name_to_delete'] ?? null;

        // Fetch the meal plan data again after the delete action
        $meal_plan_data = $meal_plan_response['data']['plan'] ?? [];
        // Delete the recipe or meal from the plan
        if ($recipe_name_to_delete) {
            unset($meal_plan_data[$day_to_delete][$meal_name_to_delete][array_search($recipe_name_to_delete,$meal_plan_data[$day_to_delete][$meal_name_to_delete])]);
        } else {
            unset($meal_plan_data[$day_to_delete][$meal_name_to_delete]);
        }

        $meal_plan_response['data']['plan'] = $meal_plan_data;

        // Prepare the updated data to be sent to the API
        $payload = ['plan' => $meal_plan_response['data']['plan']];

        // Send the updated meal plan to the API
        $update_response = api_request_with_token("api/users/$user_id/meal-plan", 'PATCH', $payload);

        if (isset($update_response['code']) && $update_response['code'] === 200) {
            header("Location: meal_plan.php");
            exit();
        } else {
            $error_message = "Failed to update meal plan after deletion.";
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
        .meal-box {
            margin-bottom: 15px;
        }
        .meal-box:last-child {
            margin-bottom: 0;
        }
        .nav-tabs {
            margin-bottom: 20px;
        }
        .btn {
            margin-top: 10px;
        }
        .modal-header {
            background-color: #e9ecef;
            border-bottom: 1px solid #ddd;
        }
        .modal-title {
            font-size: 18px;
        }
        .delete-btn {
            color: red;
            font-weight: bold;
            cursor: pointer;
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <?php echo($NAV_ICONS) ?>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Your Weekly Meal Plan</h1>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <!-- Days of the Week Navigation -->
        <ul class="nav nav-tabs justify-content-center" id="mealPlanTabs" role="tablist">
            <?php foreach ($days_of_week as $index => $day): ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?php echo $index == 0 ? 'active' : ''; ?>" id="<?php echo strtolower($day); ?>-tab" data-bs-toggle="tab" data-bs-target="#<?php echo strtolower($day); ?>" type="button" role="tab" aria-controls="<?php echo strtolower($day); ?>" aria-selected="<?php echo $index == 0 ? 'true' : 'false'; ?>">
                        <?php echo $day; ?>
                    </button>
                </li>
            <?php endforeach; ?>
        </ul>

        <!-- Meals for Each Day -->
        <div class="tab-content mt-4" id="mealPlanTabContent">
            <?php foreach ($days_of_week as $index => $day): ?>
                <div class="tab-pane fade <?php echo $index == 0 ? 'show active' : ''; ?>" id="<?php echo strtolower($day); ?>" role="tabpanel" aria-labelledby="<?php echo strtolower($day); ?>-tab">
                    <div class="meal-plan-box">
                        <h5 class="text-center"><?php echo $day; ?> Meals</h5>

                        <?php if (!empty($display[$day])): ?>
                            <?php foreach ($display[$day] as $meal_name => $recipes): ?>
                                <div class="meal-box">
                                    <h6><?php echo htmlspecialchars($meal_name); ?>
                                        <!-- Delete Meal Button -->
                                        <form method="POST" action="" style="display:inline;">
                                            <input type="hidden" name="day_to_delete" value="<?php echo $day; ?>">
                                            <input type="hidden" name="meal_name_to_delete" value="<?php echo $meal_name; ?>">
                                            <button type="submit" name="delete_meal" class="delete-btn">X</button>
                                        </form>
                                    </h6>
                                    <?php if (!empty($recipes)): ?>
                                        <ul>
                                            <?php foreach ($recipes as $recipe): ?>
                                                <li>
                                                    <a href="recipe.php?id=<?php echo explode("|", $recipe)[1]; ?>"><?php echo htmlspecialchars(explode("|", $recipe)[0]); ?></a>
                                                    <!-- Delete Recipe Button -->
                                                    <form method="POST" action="" style="display:inline;">
                                                        <input type="hidden" name="day_to_delete" value="<?php echo $day; ?>">
                                                        <input type="hidden" name="meal_name_to_delete" value="<?php echo $meal_name; ?>">
                                                        <input type="hidden" name="recipe_name_to_delete" value="<?php echo $recipe;?>">
                                                        
                                                        <button type="submit" name="delete_meal" class="delete-btn">X</button>
                                                    </form>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php else: ?>
                                        <p>No recipes added for this meal yet.</p>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No meals planned for <?php echo htmlspecialchars($day); ?> yet.</p>
                        <?php endif; ?>

                        <!-- Add Meal Button -->
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMealModal<?php echo $index; ?>">Add Meal</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Modal for Adding Meals -->
        <?php foreach ($days_of_week as $index => $day): ?>
            <div class="modal fade" id="addMealModal<?php echo $index; ?>" tabindex="-1" aria-labelledby="addMealModalLabel<?php echo $index; ?>" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addMealModalLabel<?php echo $index; ?>">Add Meal for <?php echo $day; ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form method="POST" action="">
                                <input type="hidden" name="day" value="<?php echo $day; ?>">
                                <div class="mb-3">
                                    <label for="meal_name" class="form-label">Meal Name</label>
                                    <input type="text" class="form-control" id="meal_name" name="meal_name" placeholder="Enter meal name" required>
                                </div>
                                <button type="submit" name="add_meal" class="btn btn-primary">Add Meal</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php echo($NAV_FOOTER) ?>
</body>
</html>