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

// Fetch projects associated with the client
try {
    $user_id = $_SESSION['user_id'];
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
    <title>Client Reports Dashboard - TrackBuild</title>
    <link rel="icon" href="../../assets/logo.jpg" type="image/png">
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
            border-radius: 10px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 1rem;
        }
        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
        .card-title {
            color: rgb(0, 20, 41);
            transition: color 0.3s ease;
        }
        .card-title:hover {
            color: #0056b3;
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            transform: scale(1.05);
        }
        .alert-link {
            text-decoration: underline;
            color: #0056b3;
        }
        .alert-link:hover {
            color: #003f7f;
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
                        <a class="nav-link" href="../account/_client_account_details.php">Account</a>
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
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="../client_dashboard.php">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Reports</li>
        </ol>
    </nav>

    <!-- Main Content -->
    <div class="container mt-5">
        <h2>Client Reports Dashboard</h2>
        <p class="lead">Here are the projects you are associated with. Select a project to generate detailed reports.</p>
        <hr>

        <!-- Project List -->
        <div class="row">
            <?php if (!empty($projects)): ?>
                <?php foreach ($projects as $project): ?>
                    <div class="col-12">
                        <div class="card shadow-sm h-100">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($project['project_name']) ?></h5>
                                <p class="card-text">Managed by: <?= htmlspecialchars($project['engineer_name']) ?></p>
                                <a href="client_generate_report.php?project_id=<?= $project['id'] ?>" class="btn btn-primary btn-sm">
                                    View Reports
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info text-center" role="alert">
                        No projects found. Please contact your engineer to be associated with a project.
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <?php include '../../includes/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
