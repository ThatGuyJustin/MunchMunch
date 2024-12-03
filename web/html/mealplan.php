<?php
require_once 'util.php'; // Include utility functions
require_once 'nav.php';
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Dummy data representing the meal plan
$meal_plan = [
    [
        ["title" => "Breakfast", "recipes" => [["id" => 1, "name" => "Pancakes"]]],
        ["title" => "Lunch", "recipes" => [["id" => 2, "name" => "Caesar Salad"]]],
        ["title" => "Dinner", "recipes" => [["id" => 3, "name" => "Spaghetti Bolognese"]]]
    ],
    [
        ["title" => "Breakfast", "recipes" => [["id" => 4, "name" => "Omelette"]]],
        ["title" => "Lunch", "recipes" => [["id" => 5, "name" => "Grilled Cheese"]]],
        ["title" => "Dinner", "recipes" => [["id" => 6, "name" => "Chicken Stir-fry"]]]
    ],
    // Repeat similar data for other days of the week
];

// Days of the week for display
$days_of_week = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php echo($NAV_HEADERS); ?>
    <meta charset="UTF-8">
    <title>Meal Plan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .meal-plan-box {
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 10px;
            background-color: #f8f9fa;
            margin-top: 20px;
        }
        .centered-title {
            text-align: center;
            margin-top: 20px;
        }
        .meal-title {
            font-weight: bold;
        }
        .day-column {
            margin-top: 10px;
        }
    </style>
</head>
<body>
<?php echo($NAV_ICONS); ?>

<div class="container mt-5">
    <h2 class="centered-title">Weekly Meal Plan</h2>
    <div class="row">
        <?php foreach ($days_of_week as $index => $day): ?>
            <div class="col-md-4 day-column">
                <div class="meal-plan-box">
                    <h4><?php echo htmlspecialchars($day); ?></h4>
                    <?php if (isset($meal_plan[$index])): ?>
                        <ul class="list-group">
                            <?php foreach ($meal_plan[$index] as $meal): ?>
                                <li class="list-group-item">
                                    <div class="meal-title"><?php echo htmlspecialchars($meal['title']); ?></div>
                                    <?php if (!empty($meal['recipes'])): ?>
                                        <ul>
                                            <?php foreach ($meal['recipes'] as $recipe): ?>
                                                <li>
                                                    <a href="/recipe.php?id=<?php echo htmlspecialchars($recipe['id']); ?>">
                                                        <?php echo htmlspecialchars($recipe['name']); ?>
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php else: ?>
                                        <small>No recipes assigned.</small>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>No meals planned for this day.</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

</body>
</html>
