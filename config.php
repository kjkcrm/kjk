<?php
// Database configuration details
define('DB_SERVER', 'localhost');  // Database server (usually localhost)
define('DB_USERNAME', 'root');     // Database username
define('DB_PASSWORD', '');         // Database password (leave empty for default if no password)
define('DB_NAME', 'kjk');       // Name of your database

// Create a database connection
function getDbConnection() {
    // Create connection
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

    // Check if connection was successful
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    return $conn;
}
?>
