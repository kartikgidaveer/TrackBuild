<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start the session
session_start(); 

// Unset all session variables
session_unset(); 

// Destroy the session
session_destroy(); 

// Redirect to login page (adjust path as needed)
header("Location: index.php"); 
exit();
?>
