<?php
require_once '../config/db.php'; // Include the database configuration file

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get user input
    $username_or_email = trim($_POST['username_or_email']);
    $input_password = trim($_POST['password']); // Raw password input

    // Check if inputs are empty
    if (empty($username_or_email) || empty($input_password)) {
        $error = "Please enter your username/email and password.";
        header("Location: ../views/login.php?error=" . urlencode($error));
        exit();
    }

    try {
        // Query to check if the user exists by username or email
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username_or_email OR email = :username_or_email");
        $stmt->bindParam(':username_or_email', $username_or_email);
        $stmt->execute();

        // Fetch user data
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Verify the password
            if (password_verify($input_password, $user['password'])) {
                // Password is correct; set session variables
                session_start();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = strtolower($user['role_name']); // Store role in session

                regenerateSession(); // Regenerate session ID for security

                // Redirect based on role
                if ($_SESSION['role'] === 'engineer') {
                    header("Location: ../views/engineer_dashboard.php");
                } elseif ($_SESSION['role'] === 'client') {
                    header("Location: ../views/client_dashboard.php");
                } else {
                    // Invalid role, deny access
                    $error = "Invalid role assigned. Please contact the administrator.";
                    header("Location: ../views/login.php?error=" . urlencode($error));
                }
                exit();
            } else {
                $error = "Incorrect password. Please try again.";
            }
        } else {
            $error = "No user found with that username or email.";
        }
    } catch (PDOException $e) {
        // Log the error
        error_log(
            "[" . date('Y-m-d H:i:s') . "] Login error: " . $e->getMessage() . PHP_EOL,
            3,
            __DIR__ . '/../logs/db_errors.log' // Ensure the logs directory exists
        );
        $error = "An error occurred. Please try again later.";
    }

    // Redirect back to login page with error
    if (isset($error)) {
        header("Location: ../views/login.php?error=" . urlencode($error));
        exit();
    }
} else {
    header("Location: ../views/login.php");
    exit();
}
?>
