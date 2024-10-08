<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <!-- font awesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <title>Dashboard</title>
   
    <style>
        body 
        {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }

        .menu 
        {
            position: fixed;
            top: 10px;
            right: 20px;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }


        .profile-icon 
        {
    background-color: #007bff;
    color: white;
    padding: 12px; 
    border-radius: 50%;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center; 
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    font-size: 22px;
    cursor: pointer;
    user-select: none;
}

        .profile-icon:hover 
        {
            background-color: #0056b3;
        }

        .dropdown 
        {
            display: none;
            position: absolute;
            top: 50px;
            right: 0;
            background-color: white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
            z-index: 1;
            user-select: none;
        }

        .dropdown a 
        {
            display: flex;
            align-items: center;
            padding: 10px 20px;
            text-decoration: none;
            color: white;
            background-color: #6c757d;
            font-size: 14px;
            user-select: none;
        }

        .dropdown a:hover 
        {
            background-color: #5a6268;
        }

        .dropdown a i 
        {
            margin-right: 8px;
        }

        /* Search button on the left */
        .search-button 
        {
            position: fixed;
            top: 10px;
            left: 20px;
            background-color: #28a745;
            color: white;
            padding: 8px 16px;
            border-radius: 50px;
            text-decoration: none;
            display: flex;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            font-size: 14px;
            user-select: none;
        }

        .search-button:hover 
        {
            background-color: #218838;
        }

        .search-button i 
        {
            margin-right: 8px;
        }

    </style>
</head>
<body>

    <a href="search.php" class="search-button">
        <i class="fas fa-search"></i>Search
    </a>

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
        function toggleDropdown() 
        {
            const dropdown = document.getElementById('dropdown-menu');
            event.preventDefault();
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        }

        window.onclick = function(event)
         {
            if (!event.target.matches('.profile-icon')) 
            {
                const dropdown = document.getElementById('dropdown-menu');
                if (dropdown.style.display === 'block')
                 {
                    dropdown.style.display = 'none';
                }
            }
        };
    </script>

</body>
</html>
