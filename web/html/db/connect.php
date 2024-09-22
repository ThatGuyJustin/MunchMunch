<?php
// Database connection parameters
$host = 'main_database';  // Host name as per your Docker service
$dbname = 'postgres';     // Database name, adjust if different (e.g., 'foodtinder')
$username = 'postgres';   // Username as defined in your Docker setup
$password = 'BestPasswordEver69'; // Password as defined in your Docker Compose

try {
    // Create a new PDO instance with the database connection settings
    $db = new PDO("pgsql:host=$host;port=5432;dbname=$dbname", $username, $password);
    // Set PDO error mode to exception for better error handling
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Optional: Use echo to confirm connection for testing
    echo "Connected to the database successfully!";
} catch (PDOException $e) {
    // Handle connection errors
    die("Database connection failed: " . $e->getMessage());
}
?>
