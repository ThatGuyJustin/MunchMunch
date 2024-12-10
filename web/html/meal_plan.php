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


// Handling form submission for adding a recipe
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_meal'])) {

    $meal_plan_data = $meal_plan_response['data']['plan'];

    if(sizeof($meal_plan_data) == 0) 
    {
        for($i = 0; $i < 7; $i++)
        {
            array_push($meal_plan_data,[]);
        }
    }


  
    $selected_meal_plan = $_POST['meal_plan'];
    $selected_day = array_search($_POST['day'], $days_of_week);
    $meal_name = $_POST['meal_name'];
    $new_meal = [ $meal_name => [] ];
    $meal_plan_data[$selected_day] = $new_meal;

   
    
    // Prepare the data for the API request
    $payload = [
        'plan' => $meal_plan_data,
    ];

    $update_response = api_request_with_token("api/users/$user_id/meal-plan", 'PATCH', $payload);

if (isset($update_response['code']) && $update_response['code'] === 200) {
    header("Refresh:0; url=meal_plan.php");
} else {
    echo "Failed to update Meal plan. Error: ";
    exit();
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
            border-radius: 10px;
            background-color: #f8f9fa;
        }
        .meal-card {
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <?php echo($NAV_ICONS) ?>
    <div class="container mt-5">
        <h1>Your Weekly Meal Plan</h1>

        <?php if (!empty($success_message)): ?>
            <div class="message success-message"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
            <div class="message error-message"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <!-- Bootstrap Nav Tabs for Days of the Week -->
        <ul class="nav nav-tabs" id="mealPlanTabs" role="tablist">
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
                        <h5><?php echo $day; ?> Meals</h5>

                        <?php
                        // Fetch meals for the selected day (assumed response)
                        $dayIndex = array_search($day, $days_of_week);

                        $meals_for_day = $display[$dayIndex];

                        if (!empty($meals_for_day)): ?>
                            <ul class="list-group">
                                <?php foreach ($meals_for_day as $meal => $title):?>

                                    <li class="list-group-item d-flex justify-content-between align-items-center meal-card">
                                        <div>
                                            <strong><?php echo htmlspecialchars($title); ?></strong>
                                        </div>
                                    </li>
                                <?php  endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p>No meals planned for this day.</p>
                        <?php endif; ?>

                        <!-- Add Meal Button (Triggers Modal) -->
                        <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#addMealModal<?php echo $index; ?>">Add Meal</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Modal for Adding a New Meal -->
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
                                <input type="hidden" name="meal_plan" value="<?php echo htmlspecialchars($selected_meal_plan); ?>">
                                <input type="hidden" name="day" value="<?php echo $day; ?>">

                                <div class="mb-3">
                                    <label for="meal_name" class="form-label">Meal Name</label>
                                    <input type="text" class="form-control" id="meal_name" name="meal_name" required placeholder="Enter meal name">
                                </div>
                                <button type="submit" name="add_meal" class="btn btn-success">Add Meal</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

    </div>

</body>
</html>