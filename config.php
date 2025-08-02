<?php
// Database configuration details
if (!defined('DB_SERVER')) {
    define('DB_SERVER', 'localhost');  // Database server (usually localhost)
}
if (!defined('DB_USERNAME')) {
    define('DB_USERNAME', 'root');     // Database username
}
if (!defined('DB_PASSWORD')) {
    define('DB_PASSWORD', '');         // Database password (leave empty for default if no password)
}
if (!defined('DB_NAME')) {
    define('DB_NAME', 'kjknew');          // Name of your database
}

// Create a database connection
if (!function_exists('getDbConnection')) {
    function getDbConnection() {
        // Create connection
        $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

        // Check if connection was successful
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        return $conn;
    }
}
?>
