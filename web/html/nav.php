<?php 

$NAV_HEADERS = <<<EOD
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
EOD;

$NAV_ICONS = <<<EOD
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php">FoodTinder</a>
        
        <!-- Search Button -->
        <form class="d-flex me-auto">
            <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search">
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
                <li><a class="dropdown-item" href="shopping_list.php"><i class="fa-solid fa-cart-shopping"></i></i>Shopping List</a></li>
                <li><a class="dropdown-item" href="notifications.php"><i class="fa-regular fa-bell"></i> Notifications</a></li>
                <li><a class="dropdown-item" href="settings.php"><i class="fa-solid fa-user-gear"></i> Settings</a></li>
                <li><a class="dropdown-item" href="recipeform.php"><i class="fa-regular fa-clipboard"></i> Create Recipe</a></li> 
                <li><a class="dropdown-item" href="contact.php"><i class="fa-solid fa-ticket"></i> Contact Admin</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="logout.php"><i class="fa-solid fa-arrow-right-from-bracket"></i> Log Out</a></li>
            </ul>
        </div>
    </div>
</nav>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="js/bootstrap.bundle.min.js"></script>
EOD;

?>