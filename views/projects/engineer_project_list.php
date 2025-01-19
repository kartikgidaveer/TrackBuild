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

// Handle project termination
if (isset($_POST['terminate_project_id'])) {
    try {
        $terminate_project_id = intval($_POST['terminate_project_id']);

        // Delete the project
        $stmt = $conn->prepare("DELETE FROM projects WHERE id = :project_id AND user_id = :user_id");
        $stmt->bindParam(':project_id', $terminate_project_id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->execute();

        header("Location: engineer_project_list.php");
        exit();
    } catch (PDOException $e) {
        $error_message = "Error terminating project: " . htmlspecialchars($e->getMessage());
    }
}

// Fetch the user's projects
try {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT * FROM projects WHERE user_id = :user_id ORDER BY created_at DESC");
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
        .project-card{border:1px solid #ddd;border-radius:10px;margin-bottom:15px;padding:15px;background-color:#fff;transition:box-shadow .3s,transform .3s}.project-card:hover{box-shadow:0 4px 15px rgba(0,0,0,.2);transform:translateY(-5px)}.project-badge{font-size:.85rem;padding:5px 10px;border-radius:5px}.badge-client{background-color:#17a2b8;color:#fff}.no-projects{text-align:center;color:#6c757d}.home-icon{width:40px;height:40px}.btn-action{margin-right:5px;transition:background-color .3s,transform .3s}.btn-primary{background-color:#007bff;border-color:#007bff}.btn-primary:hover{background-color:#0056b3;border-color:#004085;transform:scale(1.05)}.btn-danger:hover{background-color:#bd2130;border-color:#dc3545;transform:scale(1.05)}
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="../engineer_dashboard.php">
                <img src="../../assets/home-icon.png" alt="Home" class="home-icon">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../account/account_details.php">Account</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="engineer_project_list.php">Projects</a>
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
            <li class="breadcrumb-item"><a href="../engineer_dashboard.php">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Your Projects</li>
        </ol>
    </nav>

    <!-- Main Content -->
    <div class="container mt-4">
        <h2 class="mb-4">Your Projects</h2>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?= $error_message; ?></div>
        <?php endif; ?>

        <?php if (count($projects) > 0): ?>
            <div>
                <!-- Project cards displayed vertically -->
                <?php
                foreach ($projects as $project):
                    // Fetch client name using the client_id from the users table
                    $stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
                    $stmt->bindValue(1, $project['client_id'], PDO::PARAM_INT);
                    $stmt->execute();
                    $client = $stmt->fetch(PDO::FETCH_ASSOC);
                    $client_name = $client ? $client['username'] : 'Unknown Client';
                    ?>
                    <div class="project-card">
                        <h5><?= htmlspecialchars($project['project_name']); ?></h5>
                        <p class="mb-1">
                            <span class="project-badge badge-client">Client: <?= htmlspecialchars($client_name); ?> (ID:
                                <?= htmlspecialchars($project['client_id']); ?>)</span>
                        </p>
                        <p class="text-muted">Created on: <?= date('Y-m-d', strtotime($project['created_at'])); ?></p>
                        <div>
                            <a href="project_dashboard.php?project_id=<?= $project['id']; ?>"
                                class="btn btn-primary btn-action btn-sm">View</a>
                            <button class="btn btn-danger btn-action btn-sm"
                                onclick="confirmTermination(<?= $project['id']; ?>)">Terminate</button>
                        </div>
                    </div>
                <?php endforeach; ?>

            <?php else: ?>
                <p class="no-projects">No projects found. Start by creating a new project.</p>
                <a href="create_project.php" class="btn btn-success">Create New Project</a>
            <?php endif; ?>
        </div>

        <!-- Confirmation Modal -->
        <div class="modal fade" id="confirmTerminationModal" tabindex="-1"
            aria-labelledby="confirmTerminationModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="confirmTerminationModalLabel">Confirm Project Termination</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Are you sure you want to terminate this project? This action cannot be undone.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <form id="terminateProjectForm" method="POST">
                            <input type="hidden" name="terminate_project_id" id="terminate_project_id">
                            <button type="submit" class="btn btn-danger">Terminate</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <?php include('../../includes/footer.php'); ?>

        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            function confirmTermination(projectId) {
                document.getElementById('terminate_project_id').value = projectId;
                const modal = new bootstrap.Modal(document.getElementById('confirmTerminationModal'));
                modal.show();
            }
        </script>
</body>

</html>