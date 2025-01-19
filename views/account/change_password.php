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
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    try {
        // Fetch the user's current password
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = :id");
        $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($current_password, $user['password'])) {
            if ($new_password === $confirm_password) {
                // Update the password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_stmt = $conn->prepare("UPDATE users SET password = :password WHERE id = :id");
                $update_stmt->bindParam(':password', $hashed_password, PDO::PARAM_STR);
                $update_stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
                $update_stmt->execute();

                // Redirect to account_details.php on success
                header("Location: account_details.php?success=Password updated successfully");
                exit();
            } else {
                $message = "New password and confirmation do not match.";
            }
        } else {
            $message = "Current password is incorrect.";
        }
    } catch (Exception $e) {
        $message = "An error occurred. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - TrackBuild</title>
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
        }
        .card {
            background: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #343a40;
        }
        /* Responsive Styling */
        @media (max-width: 576px) {
            .container {
                margin-top: 30px;
                max-width: 100%;
            }
            .card {
                padding: 20px;
            }
            h2 {
                font-size: 1.5rem;
            }
            .btn-primary {
                padding: 12px 15px;
            }
        }
        @media (min-width: 576px) and (max-width: 768px) {
            .container {
                max-width: 100%;
            }
            h2 {
                font-size: 1.75rem;
            }
            .btn-primary {
                padding: 14px 18px;
            }
        }
        @media (min-width: 768px) {
            .container {
                max-width: 600px;
            }
            h2 {
                font-size: 2rem;
            }
            .btn-primary {
                padding: 16px 20px;
            }
        }
    </style>
</head>
<body>

    <!-- Main Content -->
    <div class="container">
        <div class="card">
            <h2 class="text-center mb-4">Change Password</h2>
            
            <!-- Display message -->
            <?php if (!empty($message)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <!-- Change Password Form -->
            <form action="change_password.php" method="POST">
                <div class="mb-3">
                    <label for="current_password" class="form-label">Current Password</label>
                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                </div>
                <div class="mb-3">
                    <label for="new_password" class="form-label">New Password</label>
                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                </div>
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Update Password</button>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
