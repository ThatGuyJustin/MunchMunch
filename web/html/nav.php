<?php 

$NAV_HEADERS = <<<EOD

    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="css/nav.css" rel="stylesheet">

EOD;

$NAV_ICONS = <<<EOD

<div id="nav" style="padding-bottom: 3%">

    <a href="search.php" class="search-button"><i class="fas fa-search"></i>Search</a>

    <div class="menu">
        <div class="profile-icon" onclick="toggleDropdown()">
            <i class="fa-regular fa-user"></i>
        </div>
        <div class="dropdown" id="dropdown-menu">
            <a href="profile.php"><i class="fa-regular fa-user"></i>Your Profile</a>
            <a href="account.php"><i class="fas fa-user-cog"></i>Account</a>
            <a href="edit-profile.php"><i class="fas fa-edit"></i>Edit Profile</a>
            <a href="notifications.php"><i class="fas fa-bell"></i>Notifications</a>
            <a href="settings.php"><i class="fas fa-cog"></i>Settings</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i>Log Out</a>
        </div>
    </div>

    <script>
    function toggleDropdown(event) 
    {
        event.stopPropagation();
        
        const dropdown = document.getElementById('dropdown-menu');
        dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
    }


    document.querySelector('.profile-icon').addEventListener('click', toggleDropdown);


    window.onclick = function(event) 
    {
        const dropdown = document.getElementById('dropdown-menu');
        
        if (!event.target.closest('.profile-icon') && !event.target.closest('#dropdown-menu')) 
        {
            if (dropdown.style.display === 'block') 
            {
                dropdown.style.display = 'none';
            }
        }
    };
    </script>
</div>

EOD;

?>
