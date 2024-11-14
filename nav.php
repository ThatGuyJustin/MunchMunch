<?php 

$NAV_HEADERS = <<<EOD
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/css/bootstrap.min.css">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
EOD;

$NAV_ICONS = <<<EOD
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php">FoodTinder</a>
        
        <!-- Combined Search Bar with Filter Dropdown -->
        <form class="d-flex me-auto" method="GET" action="search.php">
            <div class="input-group">
                <!-- Dropdown for Search Filter -->
                <div class="input-group-prepend">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="searchFilterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        Filter
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="searchFilterDropdown">
                        <li><a class="dropdown-item" href="#" onclick="setSearchFilter('recipe')">Recipe Name</a></li>
                        <li><a class="dropdown-item" href="#" onclick="setSearchFilter('tag')">Tag</a></li>
                    </ul>
                </div>
                
                <input type="hidden" name="filter" id="searchFilter" value="recipe">

                <input class="form-control" type="search" name="query" placeholder="Search" aria-label="Search">

                <button class="btn btn-outline-success" type="submit">Search</button>
            </div>
        </form>
        <div class="dropdown">
            <button class="btn btn-secondary dropdown-toggle" type="button" id="navbarDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa-regular fa-user"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                <li><a class="dropdown-item" href="account.php">Account Editing</a></li>
                <li><a class="dropdown-item" href="notifications.php">Notifications</a></li>
                <li><a class="dropdown-item" href="settings.php">Settings</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="logout.php">Log Out</a></li>
            </ul>
        </div>
    </div>
</nav>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>

<script>
    // JavaScript function to update the filter value in the hidden input
    function setSearchFilter(filter) {
        document.getElementById('searchFilter').value = filter;
    }
</script>
EOD;

?>
