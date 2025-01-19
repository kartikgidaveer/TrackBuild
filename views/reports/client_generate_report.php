<?php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['project_id'])) {
    header("Location: client_report_dashboard.php");
    exit();
}

$project_id = intval($_GET['project_id']);
$user_id = $_SESSION['user_id'];

try {
    // Validate ownership of the project for the client
    $stmt = $conn->prepare("SELECT * FROM projects WHERE id = :project_id AND client_id = :user_id");
    $stmt->bindParam(':project_id', $project_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $project = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$project) {
        header("Location: client_report_dashboard.php");
        exit();
    }

    // Fetch expenses for the project
    $stmt = $conn->prepare("SELECT * FROM expenses WHERE project_id = :project_id");
    $stmt->bindParam(':project_id', $project_id, PDO::PARAM_INT);
    $stmt->execute();
    $expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Report - TrackBuild</title>
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
            margin-top: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .table-hover tbody tr:hover {
            background-color: #f1f8ff;
        }

        .btn-primary,
        .btn-success {
            border: none;
            transition: background-color 0.2s ease-in-out, transform 0.2s ease-in-out;
        }

        .btn-primary:hover,
        .btn-success:hover {
            transform: scale(1.05);
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
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
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
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="../client_dashboard.php">Home</a></li>
            <li class="breadcrumb-item"><a href="client_report_dashboard.php">Reports</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($project['project_name']) ?>
            </li>
        </ol>
    </nav>

    <!-- Main Content -->
    <div class="container mt-5">
        <h2>Report for <?= htmlspecialchars($project['project_name']) ?></h2>
        <p class="lead">Below is the expense breakdown for the selected project. You can view the details of each
            expense.</p>
        <hr>

        <!-- Expense Table -->
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Expense ID</th>
                        <th>Category</th>
                        <th>Expense Name</th>
                        <th>Amount(INR)</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($expenses as $expense): ?>
                        <tr>
                            <td><?= $expense['id'] ?></td>
                            <td><?= htmlspecialchars($expense['category']) ?></td>
                            <td><?= htmlspecialchars($expense['expense_name'] ?? 'N/A') ?></td>
                            <td><?= number_format($expense['amount'], 2) ?></td>
                            <td><?= htmlspecialchars($expense['date']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Format Selection Buttons -->
        <div class="d-flex justify-content-start gap-3">
            <!-- PDF Button (opens in a new tab) -->
            <form action="download_report.php" method="GET" target="_blank">
                <input type="hidden" name="project_id" value="<?= $project_id ?>">
                <input type="hidden" name="format" value="pdf">
                <button type="submit" class="btn btn-primary">Download PDF</button>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <?php include '../../includes/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>