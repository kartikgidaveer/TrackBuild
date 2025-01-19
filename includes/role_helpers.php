<?php
// role_helpers.php

// Include the database configuration
require_once __DIR__ . '/../config/db.php'; // Correct path to db.php

// Function to check if a user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to check if the logged-in user is an engineer
function isEngineer() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'engineer';
}

// Function to check if the logged-in user is a client
function isClient() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'client';
}

// Function to redirect users to login if they are not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: ../views/login.php?error=" . urlencode("You must be logged in to access this page."));
        exit();
    }
}

// Function to restrict access to engineers only
function requireEngineer() {
    requireLogin(); // Ensure the user is logged in
    if (!isEngineer()) {
        header("Location: ../views/403.php?error=" . urlencode("Access denied. Engineers only."));
        exit();
    }
}

// Function to restrict access to clients only
function requireClient() {
    requireLogin(); // Ensure the user is logged in
    if (!isClient()) {
        header("Location: ../views/403.php?error=" . urlencode("Access denied. Clients only."));
        exit();
    }
}

// Function to check if the logged-in user has access to a specific project
function checkAccess($project_id, $role) {
    global $db;
    $user_id = $_SESSION['user_id'];  // Get the current logged-in user ID
    
    // Query based on user role
    if ($role === 'engineer') {
        $query = "SELECT * FROM client_engineer_projects WHERE engineer_id = ? AND project_id = ?";
    } elseif ($role === 'client') {
        $query = "SELECT * FROM client_engineer_projects WHERE client_id = ? AND project_id = ?";
    } else {
        return false;  // Invalid role, deny access
    }

    // Prepare and execute the query
    $stmt = $db->prepare($query);
    $stmt->execute([$user_id, $project_id]);

    // Check if the user has access to the project
    return $stmt->rowCount() > 0;
}

// Function to restrict access to a specific project for engineers
function requireEngineerProjectAccess($project_id) {
    if (!checkAccess($project_id, 'engineer')) {
        header("Location: ../views/403.php?error=" . urlencode("Access denied. You do not have permission to view this project."));
        exit();
    }
}

// Function to restrict access to a specific project for clients
function requireClientProjectAccess($project_id) {
    if (!checkAccess($project_id, 'client')) {
        header("Location: ../views/403.php?error=" . urlencode("Access denied. You do not have permission to view this project."));
        exit();
    }
}

// Regenerate session ID securely
function secureSessionRegeneration() {
    regenerateSession(); // Call the session regeneration function from db.php
}
?>
