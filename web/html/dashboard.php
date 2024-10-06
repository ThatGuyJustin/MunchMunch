<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <!-- font awesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <title>Dashboard</title>
   
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }

        /* Profile button and menu on the right */
        .profile-menu {
            position: fixed;
            top: 10px;
            right: 20px;
            display: flex;
            flex-direction: column;
        }

        .profile-button {
            background-color: #007bff;
            color: white;
            padding: 8px 16px;
            border-radius: 50px;
            text-decoration: none;
            display: flex;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            font-size: 14px;
            margin-bottom: 10px;
        }

        .profile-button:hover {
            background-color: #0056b3;
        }

        .profile-button i {
            margin-right: 8px;
        }

        .menu-button {
            background-color: #6c757d;
            color: white;
            padding: 8px 16px;
            border-radius: 50px;
            text-decoration: none;
            display: flex;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            font-size: 14px;
            margin-bottom: 10px;
        }

        .menu-button:hover {
            background-color: #5a6268;
        }

        .menu-button i {
            margin-right: 8px;
        }

        /* Search button on the left */
        .search-button {
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
        }

        .search-button:hover {
            background-color: #218838;
        }

        .search-button i {
            margin-right: 8px;
        }

    </style>
</head>
<body>

    <!-- Search button on the left -->
    <a href="search.php" class="search-button">
        <i class="fas fa-search"></i>Search
    </a>

    <!-- Profile and menu buttons on the right -->
    <div class="profile-menu">
        <a href="profile.php" class="profile-button">
            <i class="fa-regular fa-user"></i>Your Profile
        </a>
        <a href="account.php" class="menu-button">
            <i class="fas fa-user-cog"></i>Account
        </a>
        <a href="edit-profile.php" class="menu-button">
            <i class="fas fa-edit"></i>Edit Profile
        </a>
        <a href="notifications.php" class="menu-button">
            <i class="fas fa-bell"></i>Notifications
        </a>
        <a href="settings.php" class="menu-button">
            <i class="fas fa-cog"></i>Settings
        </a>
        <a href="logout.php" class="menu-button">
            <i class="fas fa-sign-out-alt"></i>Log Out
        </a>
    </div>

</body>
</html>
