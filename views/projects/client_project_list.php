<?php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Initialize variables
$projects = [];
$error_message = "";

// Fetch the user's projects
try {
    $user_id = $_SESSION['user_id'];
    // Update the query to fetch projects along with the engineer's name
    $stmt = $conn->prepare("SELECT p.*, u.username AS engineer_name FROM projects p 
                            JOIN users u ON p.engineer_id = u.id 
                            WHERE p.client_id = :user_id ORDER BY p.created_at DESC");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Error fetching projects: " . htmlspecialchars($e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Projects - TrackBuild</title>
    <link rel="icon" href="../../assets/logo.jpg" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .navbar-custom {
            background-color: #f0f8ff;
            border-bottom: 1px solid #e0e0e0;
        }
        .list-group-item-action {
            transition: background-color 0.2s ease-in-out;
        }
        .list-group-item-action:hover {
            background-color: #f8f9fa;
        }
        .project-badge {
            background-color: #ffcc00;
            color: #000;
        }
        .no-projects {
            color: #888;
            font-style: italic;
        }
        .home-icon {
            width: 40px;
            height: 40px;
        }

        /* Responsive adjustments */
        @media (max-width: 767px) {
            .navbar-nav {
                text-align: center;
            }

            .home-icon {
                width: 30px;
                height: 30px;
            }

            .list-group-item {
                padding: 10px;
            }

            .btn-sm {
                font-size: 12px;
                padding: 5px 10px;
            }

            .no-projects {
                font-size: 14px;
            }

            .container {
                padding: 10px;
            }
        }

        @media (max-width: 576px) {
            .list-group-item {
                font-size: 14px;
            }
            .btn-primary {
                width: 100%;
                margin-top: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="../engineer_dashboard.php">
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
                        <a class="nav-link" href="client_project_list.php">Projects</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="../../logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="../engineer_dashboard.php">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Your Projects</li>
        </ol>
    </nav>

    <!-- Main Content -->
    <div class="container mt-5">
        <h2>Your Projects</h2>
        <p class="lead">Manage and track expenses for your construction projects.</p>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <?php if (count($projects) > 0): ?>
            <div class="list-group">
                <?php foreach ($projects as $project): ?>
                    <div class="list-group-item list-group-item-action">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?php echo htmlspecialchars($project['project_name']); ?></strong>
                                <p class="mb-0 text-muted">Created on: <?php echo date('Y-m-d', strtotime($project['created_at'])); ?></p>
                                <p class="mb-0 text-muted">Engineer: <?php echo htmlspecialchars($project['engineer_name']); ?></p>
                            </div>
                            <div>
                                <a href="client_project_dashboard.php?project_id=<?php echo $project['id']; ?>" class="btn btn-primary btn-sm">View</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="no-projects mt-3">You currently have no projects assigned to any engineers. Assign a project to an engineer to get started and track your progress efficiently.</p>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <?php include('../../includes/footer.php'); ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
