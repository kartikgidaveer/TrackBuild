<?php
require_once '../config/db.php'; // Include the database configuration file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data and sanitize
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']); // Raw password input from the user
    $role = isset($_POST['role']) ? $_POST['role'] : 'client'; // Default role is 'client'
    
    // Validate role input (ensure it's either 'engineer' or 'client')
    if (!in_array($role, ['engineer', 'client'])) {
        header("Location: ../views/signup.php?error=Invalid role selected");
        exit();
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_BCRYPT); // Securely hash the password

    try {
        // Check if the user already exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username OR email = :email");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            header("Location: ../views/signup.php?error=User already exists");
            exit();
        }

        // Insert the new user into the database with the selected role
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role_name) VALUES (:username, :email, :password, :role_name)");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashed_password); // Use the hashed password
        $stmt->bindParam(':role_name', $role); // Bind the role

        if ($stmt->execute()) {
            // Redirect to login page on successful registration
            header("Location: ../views/login.php");
        } else {
            header("Location: ../views/signup.php?error=Registration failed");
        }
    } catch (PDOException $e) {
        // Log the error
        error_log(
            "[" . date('Y-m-d H:i:s') . "] Signup error: " . $e->getMessage() . PHP_EOL,
            3,
            __DIR__ . '/../logs/db_errors.log' // Ensure the logs directory exists
        );
        // Redirect with a generic error message
        header("Location: ../views/signup.php?error=An error occurred. Please try again later.");
    }
    exit();
}
?>
