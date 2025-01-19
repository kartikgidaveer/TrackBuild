<?php
session_start();
require_once '../../config/db.php'; // Adjust path if needed

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details using PDO
try {
    $stmt = $conn->prepare("SELECT id, username, email, role_name FROM users WHERE id = :id");
    $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception("User not found.");
    }
} catch (Exception $e) {
    die("Error fetching user details: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Details - TrackBuild</title>
    <link rel="icon" href="../../assets/logo.jpg" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Base Styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }

        .container {
            margin-top: 40px;
        }

        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .breadcrumb {
            background-color: #f9f9f9;
            border-radius: 5px;
        }

        .info-group {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 15px;
        }

        .btn-warning {
            background-color: #ffcc00; /* Warm yellow for buttons */
            border-color: #e6b800;
        }

        .btn-warning:hover {
            background-color: #e6b800; /* Darker yellow on hover */
        }

        /* Navbar Styling */
        .navbar-brand img {
            width: 40px;
            height: 40px;
        }

        /* Mobile-first Design */
        @media (max-width: 576px) {
            .navbar-toggler {
                border-color: transparent;
            }

            .navbar-nav {
                text-align: center;
            }

            .navbar-nav .nav-item {
                margin-bottom: 10px;
            }

            .navbar-nav .nav-link {
                font-size: 1.1rem;
            }

            .card {
                padding: 15px;
            }

            .breadcrumb {
                font-size: 0.9rem;
            }
        }

        /* Tablet Screens */
        @media (min-width: 576px) and (max-width: 768px) {
            .navbar-nav .nav-link {
                font-size: 1.2rem;
            }

            .card {
                padding: 20px;
            }
        }

        /* Desktop Screens */
        @media (min-width: 768px) {
            .navbar-nav .nav-link {
                font-size: 1rem;
            }

            .card {
                padding: 30px;
            }

            .breadcrumb {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="../client_dashboard.php">
            <img src="../../assets/home-icon.png" alt="Home" class="home-icon">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="../account/client_account_details.php">Account</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../projects/client_project_list.php">Projects</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-danger" href="../../logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Breadcrumbs -->
<nav aria-label="breadcrumb" class="mt-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="../client_dashboard.php">Home</a></li>
        <li class="breadcrumb-item active" aria-current="page">Account Details</li>
    </ol>
</nav>

<!-- Account Details -->
<div class="container">
    <div class="card p-4">
        <h2 class="mb-3">Your Account Details</h2>
        <p class="lead">Manage your account information below.</p>

        <div class="info-group">
            <span><strong>User ID:</strong> <?php echo htmlspecialchars($user['id']); ?></span>
        </div>

        <div class="info-group">
            <span><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></span>
        </div>

        <div class="info-group">
            <span><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></span>
        </div>

        <div class="info-group">
            <span><strong>Role:</strong> <?php echo htmlspecialchars($user['role_name']); ?></span>
        </div>

        <div class="info-group">
            <a href="change_password.php" class="btn btn-primary">Change Password</a>
        </div>
    </div>
</div>

<!-- Footer -->
<?php include('../../includes/footer.php'); ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
