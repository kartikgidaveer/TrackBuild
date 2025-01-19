<?php
// Database configuration
$host = 'localhost';      // Database host
$dbname = 'trackbuild_db'; // Database name
$username = 'root';       // Database username (default for XAMPP)
$password = '';           // Database password (default is empty for XAMPP)

// Secure session start
if (session_status() == PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,    // Prevent JavaScript access to session cookies
        'cookie_secure' => isset($_SERVER['HTTPS']), // Ensure cookies are sent over HTTPS
        'cookie_samesite' => 'Strict', // Restrict cookies to same-site requests
    ]);
}

// Create a PDO connection
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Set error mode to exceptions
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Regenerate session ID after login or other sensitive actions
function regenerateSession() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true); // Regenerate the session ID and delete the old one
    }
}
?>
