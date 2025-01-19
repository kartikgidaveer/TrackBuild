<?php
session_start();
require_once '../../config/db.php';

// Redirect if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$message = ''; // Initialize message variable

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $new_email = trim($_POST['new_email']);

    if (filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        try {
            // Prepare the SQL statement
            $stmt = $conn->prepare("UPDATE users SET email = :new_email WHERE id = :user_id");
            $stmt->bindParam(':new_email', $new_email, PDO::PARAM_STR);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);

            // Execute the query
            if ($stmt->execute()) {
                // Redirect to account_details.php with success message
                header("Location: account_details.php?success=Email updated successfully");
                exit();
            } else {
                $message = "Failed to update email. Please try again.";
            }
        } catch (PDOException $e) {
            $message = "Database error: " . $e->getMessage();
        }
    } else {
        $message = "Invalid email format.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Email - TrackBuild</title>
    <link rel="icon" href="../../assets/logo.jpg" type="image/png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar {
            background-color: #343a40; /* Dark theme for navbar */
        }
        .navbar-brand, .nav-link {
            color: #ffffff !important;
        }
        .container {
            margin-top: 50px;
            max-width: 600px;
            background: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #343a40;
        }
    </style>
</head>
<body>
   

    <!-- Main Content -->
    <div class="container">
        <h2 class="text-center mb-4">Change Email</h2>

        <!-- Display message -->
        <?php if (!empty($message)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <!-- Change Email Form -->
        <form action="change_email.php" method="POST">
            <div class="mb-3">
                <label for="new_email" class="form-label">New Email</label>
                <input type="email" class="form-control" id="new_email" name="new_email" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Update Email</button>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
