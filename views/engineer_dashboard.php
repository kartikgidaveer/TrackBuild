<?php 
session_start();
require_once '../config/db.php';
require_once '../includes/role_helpers.php';

// Validate session and role
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: ../views/login.php?error=" . urlencode("You must be logged in to access the dashboard."));
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Redirect to the correct dashboard based on role
if ($role === 'engineer' && basename($_SERVER['PHP_SELF']) !== 'engineer_dashboard.php') {
    header("Location: ../views/engineer_dashboard.php");
    exit();
} elseif ($role === 'client' && basename($_SERVER['PHP_SELF']) !== 'client_dashboard.php') {
    header("Location: ../views/client_dashboard.php");
    exit();
}

// Fetch user details
$stmt = $conn->prepare("SELECT username FROM users WHERE id = :user_id");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header("Location: ../views/login.php?error=" . urlencode("User not found."));
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Engineer Dashboard - TrackBuild</title>
    <link rel="icon" href="../assets/logo.jpg" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .home-icon {
            width: 40px;
            height: 40px;
        }
        .navbar-custom {
            background-color: #f0f8ff;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(17, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
        .card-title {
            color:rgb(14, 14, 14);
            transition: color 0.3s ease;
        }
        .card-title:hover {
            color: #0056b3;
        }
        .btn-primary, .btn-success, .btn-warning {
            transition: background-color 0.3s ease, transform 0.3s ease;
        }
        .btn-primary:hover, .btn-success:hover, .btn-warning:hover {
            transform: scale(1.05);
        }
        .breadcrumb {
            background-color: #f8f9fa;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="engineer_dashboard.php">
                <img src="../assets/home-icon.png" alt="Home" class="home-icon">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../views/account/account_details.php">Account</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../views/projects/engineer_project_list.php">Projects</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="../logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb" class="mt-3">
        <div class="container">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="engineer_dashboard.php">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
            </ol>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-5">
        <h2>Welcome to Your Dashboard, <?php echo htmlspecialchars($user['username']); ?>!</h2>
        <p class="lead">Track and manage your construction projects with ease.</p>
        <hr>

        <!-- Dashboard Features -->
        <h3 class="mt-5">Dashboard Features</h3>
        <div class="row">
            <div class="col-md-6 col-lg-4">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Start a New Project</h5>
                        <p class="card-text">Begin a new construction project and start tracking expenses right away.</p>
                        <a href="projects/create_project.php" class="btn btn-primary">Create Project</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Manage Your Projects</h5>
                        <p class="card-text">View, edit, and manage your existing construction projects.</p>
                        <a href="projects/engineer_project_list.php" class="btn btn-success">View Projects</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Download Reports</h5>
                        <p class="card-text">Generate and download detailed reports for your projects, including expenses and budget summaries.</p>
                        <a href="reports/report_dashboard.php" class="btn btn-warning">Generate Report</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include('../includes/footer.php'); ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
