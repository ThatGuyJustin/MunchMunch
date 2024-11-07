<?php
require_once 'util.php';
require_once 'nav.php';

start_session();

if (!is_user_logged_in()) {
    header('Location: login.php');
    exit();
}

$recipes_data = [
    '1' => [
        'title' => 'Chocolate Cake',
        'ingredients' => [
            'Flour' => ['amount' => 200, 'unit' => 'grams'],
            'Sugar' => ['amount' => 100, 'unit' => 'grams'],
            'Butter' => ['amount' => 50, 'unit' => 'grams'],
            'Eggs' => ['amount' => 2, 'unit' => 'units']
        ]
    ],
    '2' => [
        'title' => 'Pancakes',
        'ingredients' => [
            'Flour' => ['amount' => 150, 'unit' => 'grams'],
            'Sugar' => ['amount' => 50, 'unit' => 'grams'],
            'Butter' => ['amount' => 100, 'unit' => 'grams'],
            'Milk' => ['amount' => 200, 'unit' => 'ml']
        ]
    ],
    '3' => [
        'title' => 'Vanilla Custard',
        'ingredients' => [
            'Flour' => ['amount' => 100, 'unit' => 'grams'],
            'Eggs' => ['amount' => 3, 'unit' => 'units'],
            'Vanilla' => ['amount' => 5, 'unit' => 'ml'],
            'Milk' => ['amount' => 100, 'unit' => 'ml']
        ]
    ]
];

$recipe_ids = ['1', '2', '3'];
$all_ingredients = [];
$recipe_titles = [];

foreach ($recipe_ids as $recipe_id) {
    if (isset($recipes_data[$recipe_id])) {
        $recipe_titles[] = $recipes_data[$recipe_id]['title'];
        
        foreach ($recipes_data[$recipe_id]['ingredients'] as $ingredient => $data) {
            $amount = $data['amount'];
            $unit = $data['unit'];
            
            if (isset($all_ingredients[$ingredient])) {
                $all_ingredients[$ingredient]['amount'] += $amount;
            } else {
                $all_ingredients[$ingredient] = ['amount' => $amount, 'unit' => $unit];
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php echo($NAV_HEADERS); ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .bordered-box {
            border: 1px solid #dee2e6; 
            border-radius: 8px; 
            padding: 15px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
<?php echo($NAV_ICONS); ?>
<div class="container mt-5">

    <h1 class="display-4 text-center">Shopping List</h1>
    <p class="lead text-center">Based on your selected recipes</p>

    <div class="row">
        <div class="col-md-6">
            <div class="bordered-box">
                <h3>Ingredients</h3>
                <ul class="list-group list-group-flush">
                    <?php foreach ($all_ingredients as $ingredient => $data): ?>
                        <li class="list-group-item">
                            <?php echo htmlspecialchars($ingredient) . ": " . htmlspecialchars($data['amount']) . " " . htmlspecialchars($data['unit']); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="bordered-box">
                <h3>Included Recipes</h3>
                <ul class="list-group list-group-flush">
                    <?php foreach ($recipe_titles as $title): ?>
                        <li class="list-group-item"><?php echo htmlspecialchars($title); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
