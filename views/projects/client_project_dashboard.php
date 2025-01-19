<?php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['project_id'])) {
    header("Location: ../login.php");
    exit();
}

// Initialize variables
$project_id = $_GET['project_id'];
$user_id = $_SESSION['user_id'];
$project = [];
$expenses = [];
$error_message = "";

// Fetch project details and expenses
try {
    // Fetch project details
    $stmt = $conn->prepare("SELECT p.*, u.username AS engineer_name 
                            FROM projects p 
                            JOIN users u ON p.engineer_id = u.id 
                            WHERE p.id = :project_id AND p.client_id = :user_id");
    $stmt->bindParam(':project_id', $project_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $project = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$project) {
        $error_message = "Project not found or access denied.";
    } else {
        // Fetch expenses for the project
        $stmt = $conn->prepare("SELECT * FROM expenses WHERE project_id = :project_id ORDER BY date DESC");
        $stmt->bindParam(':project_id', $project_id, PDO::PARAM_INT);
        $stmt->execute();
        $expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $error_message = "Error fetching project details: " . htmlspecialchars($e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Dashboard - TrackBuild</title>
    <link rel="icon" href="../../assets/logo.jpg" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .navbar-custom {
            background-color: #f0f8ff;
            border-bottom: 1px solid #e0e0e0;
        }
        .project-details {
            background-color: #f9f9f9;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .chart-container {
            width: 100%;
            height: 400px;
        }
        .expense-table {
            max-height: 300px;
            overflow-y: auto;
        }
        .no-expenses {
            color: #888;
            font-style: italic;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="../client_dashboard.php">
                <img src="../../assets/home-icon.png" alt="Home" class="home-icon" style="width: 40px; height: 40px;">
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
            <li class="breadcrumb-item"><a href="../client_dashboard.php">Home</a></li>
            <li class="breadcrumb-item"><a href="client_project_list.php">Projects</a></li>
            <li class="breadcrumb-item active" aria-current="page">Project Dashboard</li>
        </ol>
    </nav>

   <!-- Main Content -->
<div class="container mt-5">
    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger">
            <?php echo $error_message; ?>
        </div>
    <?php else: ?>
        <!-- Project Details -->
        <div class="project-details">
            <h2><?php echo htmlspecialchars($project['project_name']); ?></h2>
            <p><strong>Description:</strong> <?php echo htmlspecialchars($project['description']); ?></p>
            <p><strong>Land Area:</strong> <?php echo htmlspecialchars($project['land_area']); ?> sq.ft.</p>
            <p><strong>Budget:</strong> ₹<?php echo number_format($project['budget'], 2); ?></p>
            <p><strong>Engineer:</strong> <?php echo htmlspecialchars($project['engineer_name']); ?></p>
            <p><strong>Created On:</strong> <?php echo date('Y-m-d', strtotime($project['created_at'])); ?></p>
        </div>

        <!-- Expense List -->
        <h4>Expenses</h4>
        <?php if (count($expenses) > 0): ?>
            <div class="expense-table table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Category</th>
                            <th>Expense Name</th>
                            <th>Amount (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($expenses as $expense): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($expense['date']); ?></td>
                                <td><?php echo htmlspecialchars($expense['category']); ?></td>
                                <td><?php echo htmlspecialchars($expense['expense_name']); ?></td>
                                <td><?php echo number_format($expense['amount'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="no-expenses">No expenses added yet.</p>
        <?php endif; ?>

        <!-- Charts -->
        <h4>Category-wise Expense Breakdown</h4>
        <div class="row">
            <div class="col-md-6">
                <div class="chart-container">
                    <canvas id="categoryExpenseChart"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <h4>Budget vs Total Expenses</h4>
                <div class="chart-container">
                    <canvas id="budgetVsExpensesChart"></canvas>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

    <!-- Footer -->
    <?php include('../../includes/footer.php'); ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Charts Initialization -->
    <script>
        const expenses = <?php echo json_encode($expenses); ?>;
        const budget = <?php echo $project['budget']; ?>;

        // Prepare data for charts
        const categories = {};
        let totalExpenses = 0;
        expenses.forEach(expense => {
            totalExpenses += parseFloat(expense.amount);
            categories[expense.category] = (categories[expense.category] || 0) + parseFloat(expense.amount);
        });

        // Category-wise Expense Breakdown (Bar Graph)
        const categoryCtx = document.getElementById('categoryExpenseChart').getContext('2d');
        new Chart(categoryCtx, {
            type: 'bar',
            data: {
                labels: Object.keys(categories),
                datasets: [{
                    label: 'Expenses (₹)',
                    data: Object.values(categories),
                    backgroundColor: '#007bff'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                },
            }
        });

        // Budget vs Total Expenses (Pie Chart)
        const budgetCtx = document.getElementById('budgetVsExpensesChart').getContext('2d');
        new Chart(budgetCtx, {
            type: 'pie',
            data: {
                labels: ['Total Budget', 'Total Expenses'],
                datasets: [{
                    data: [budget, totalExpenses],
                    backgroundColor: ['#28a745', '#dc3545']
                }]
            }
        });
    </script>
</body>
</html>
