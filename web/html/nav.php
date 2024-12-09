<?php 

$NAV_HEADERS = <<<EOD
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
EOD;

// Start building $NAV_ICONS as a string
$NAV_ICONS = '
<nav class="navbar navbar-expand-lg navbar-light bg-light py-3">
    <div class="container-fluid">
        <!-- Brand Name -->
        <a class="navbar-brand" href="dashboard.php">
            <strong>FoodTinder</strong>
        </a>
        
        <!-- Toggle Button for Mobile View -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Collapsible Content -->
        <div class="collapse navbar-collapse" id="navbarContent">
            <!-- Search Form -->
            <form class="d-flex me-auto gap-3" action="search.php" method="GET">
                <input class="form-control" type="text" name="query" placeholder="Search by name" aria-label="Search">
                <input class="form-control" type="text" name="ingredients" placeholder="Search by ingredients (comma-separated)" aria-label="Ingredients">
                <select class="form-select" name="type" aria-label="Search Type">
                    <option value="recipe" selected>Recipe</option>
                    <option value="users">User</option>
                </select>
                <button class="btn btn-outline-success" type="submit">Search</button>
            </form>

            <!-- Right Side Dropdown -->
            <div class="dropdown">
                <button class="btn btn-secondary dropdown-toggle" type="button" id="navbarDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa-regular fa-user"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                    <li><a class="dropdown-item" href="profile.php"><i class="fa-regular fa-user"></i> Profile</a></li>
                    <li><a class="dropdown-item" href="account.php"><i class="fa-regular fa-pen-to-square"></i> Account Editing</a></li>
                    <li><a class="dropdown-item" href="shopping_list.php"><i class="fa-solid fa-cart-shopping"></i> Shopping List</a></li>
                    <li><a class="dropdown-item" href="notifications.php"><i class="fa-regular fa-bell"></i> Notifications</a></li>
                    <li><a class="dropdown-item" href="settings.php"><i class="fa-solid fa-user-gear"></i> Settings</a></li>
                    <li><a class="dropdown-item" href="recipeform.php"><i class="fa-regular fa-clipboard"></i> Create Recipe</a></li>
                    <li><a class="dropdown-item" href="contact.php"><i class="fa-solid fa-ticket"></i> Contact Admin</a></li>
                    <li><a class="dropdown-item" href="support.php"><i class="fa-solid fa-ticket"></i> Support</a></li>
                    <li><hr class="dropdown-divider"></li>';

// Check if the user is an admin and add the admin link if true
if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
    $NAV_ICONS .= '
                    <li><a class="dropdown-item" href="admin.php"><i class="fa-solid fa-shield-alt"></i> Admin Dashboard</a></li>
                    <li><hr class="dropdown-divider"></li>';
}

// Finish the rest of the menu items
$NAV_ICONS .= '
                    <li><a class="dropdown-item text-danger" href="logout.php"><i class="fa-solid fa-arrow-right-from-bracket"></i> Log Out</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<!-- Include necessary scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="js/bootstrap.bundle.min.js"></script>
';

?>
