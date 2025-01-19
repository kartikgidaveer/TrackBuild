<?php
session_start();
require_once '../../config/db.php';
require_once '../../includes/role_helpers.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['project_id'])) {
    header("Location: project_list.php");
    exit();
}

$project_id = $_GET['project_id'];
$user_id = $_SESSION['user_id'];

// Fetch project details
$stmt = $conn->prepare("SELECT * FROM projects WHERE id = ? AND user_id = ?");
$stmt->bindValue(1, $project_id, PDO::PARAM_INT);
$stmt->bindValue(2, $user_id, PDO::PARAM_INT);
$stmt->execute();
$project = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$project) {
    header("Location: project_list.php");
    exit();
}

// Fetch expenses for the project
$stmt = $conn->prepare("SELECT * FROM expenses WHERE project_id = ?");
$stmt->bindValue(1, $project_id, PDO::PARAM_INT);
$stmt->execute();
$expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);
$delete_error = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : null;
// Fetch category-wise expense breakdown
$stmt = $conn->prepare("SELECT category, SUM(amount) AS category_total FROM expenses WHERE project_id = ? GROUP BY category");
$stmt->bindValue(1, $project_id, PDO::PARAM_INT);
$stmt->execute();
$category_expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate total expenses
$stmt = $conn->prepare("SELECT SUM(amount) AS total_expense FROM expenses WHERE project_id = ?");
$stmt->bindValue(1, $project_id, PDO::PARAM_INT);
$stmt->execute();
$total_expense_result = $stmt->fetch(PDO::FETCH_ASSOC);
$total_expense = (float) $total_expense_result['total_expense'];

// Calculate remaining budget
$remaining_budget = (float) $project['budget'] - $total_expense;

// Budget alert logic
$budget_alert = '';
if ($total_expense >= $project['budget']) {
    $budget_alert = 'alert-danger';  // Over budget
} elseif ($total_expense >= 0.8 * $project['budget']) {
    $budget_alert = 'alert-warning'; // Approaching budget
}
// Add this after calculating $budget_alert in project_dashboard.php
if ($budget_alert === 'alert-danger' || $budget_alert === 'alert-warning') {
    $notification_message = $budget_alert === 'alert-danger'
        ? "Budget exceeded for project: {$project['project_name']}"
        : "Budget is nearing the limit for project: {$project['project_name']}";

    // Insert notification into the database
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, project_id, message, is_read, created_at) VALUES (?, ?, ?, 0, NOW())");
    $stmt->bindValue(1, $project['client_id'], PDO::PARAM_INT); // Notify the client
    $stmt->bindValue(2, $project_id, PDO::PARAM_INT);
    $stmt->bindValue(3, $notification_message, PDO::PARAM_STR);
    $stmt->execute();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_budget'])) {
    // Update budget
    $new_budget = $_POST['new_budget'];

    if ($new_budget > $project['budget']) {
        // Ensure the new budget is higher than the current budget
        $stmt = $conn->prepare("UPDATE projects SET budget = ? WHERE id = ? AND user_id = ?");
        $stmt->bindValue(1, $new_budget, PDO::PARAM_STR);
        $stmt->bindValue(2, $project_id, PDO::PARAM_INT);
        $stmt->bindValue(3, $user_id, PDO::PARAM_INT);
        $stmt->execute();

        header("Location: project_dashboard.php?project_id=" . $project_id);
        exit();
    } else {
        // Display error if the new budget is not greater than the current budget
        $budget_error = "New budget must be greater than the current budget.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_expense'])) {
    // Add expense
    $expense_name = $_POST['expense_name'];
    $amount = $_POST['amount'];
    $category = $_POST['category'];
    $date = date("Y-m-d");
    // Check if the expense exceeds the remaining budget
    if ($amount > $remaining_budget) {
        $expense_error = "Expense exceeds the remaining budget!";
    } else {
        $stmt = $conn->prepare("INSERT INTO expenses (project_id, expense_name, amount, category, date) VALUES (?, ?, ?, ?, ?)");
        $stmt->bindValue(1, $project_id, PDO::PARAM_INT);
        $stmt->bindValue(2, $expense_name, PDO::PARAM_STR);
        $stmt->bindValue(3, $amount, PDO::PARAM_STR);
        $stmt->bindValue(4, $category, PDO::PARAM_STR);
        $stmt->bindValue(5, $date, PDO::PARAM_STR);
        $stmt->execute();

        header("Location: project_dashboard.php?project_id=" . $project_id);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($project['project_name']); ?> Dashboard - TrackBuild</title>
    <link rel="icon" href="../../assets/logo.jpg" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .alert {
            position: fixed;
            top: -100px;
            left: 0;
            right: 0;
            z-index: 9999;
            margin: 0;
            transition: top 0.5s ease;
            opacity: 0;
            transition: opacity 0.5s ease;
        }

        .alert.show {
            top: 0;
            opacity: 1;
        }


        body {
            background-color: #f8f8f8;
            color: #333
        }

        .navbar-custom {
            background-color: #4c2c92;
            border-bottom: 1px solid #e0e0e0
        }

        .navbar-dark .navbar-nav .nav-link {
            color: #fff
        }

        .navbar-dark .navbar-nav .nav-link:hover {
            color: #f1f1f1
        }

        .btn-primary {
            background-color: #00a9e0;
            border-color: #0095c8
        }

        .btn-primary:hover {
            background-color: #0095c8;
            border-color: #007ea4
        }

        .home-icon {
            width: 40px;
            height: 40px
        }

        .pie-chart-container {
            max-width: 400px;
            margin: auto
        }

        .budget-section {
            position: fixed;
            top: 100px;
            right: 20px;
            padding: 20px;
            border: 1px solid #ccc;
            background-color: #fff;
            width: 300px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, .1);
            z-index: 1000;
            transition: .3s
        }

        .budget-section:hover {
            box-shadow: 0 8px 16px rgba(0, 0, 0, .2)
        }

        .budget-section input {
            width: 100%;
            height: 35px;
            font-size: 14px
        }

        .main-content {
            margin: 0 auto;
            padding: 20px;
            max-width: 1200px
        }

        .input-field {
            height: 35px;
            font-size: 14px;
            width: 50%
        }

        .container {
            margin-top: 20px;
            padding-left: 15px;
            padding-right: 15px
        }

        .budget-section button {
            display: block;
            margin: 10px auto 0
        }

        @media (max-width:768px) {
            .input-field {
                width: 100%
            }

            .budget-section {
                width: 250px
            }

            .main-content {
                padding: 15px
            }
        }
    </style>

</head>

<body>

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

    <!-- Breadcrumb Navigation -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="../engineer_dashboard.php">Home</a></li>
            <li class="breadcrumb-item"><a href="engineer_project_list.php">Projects</a></li>
            <li class="breadcrumb-item active" aria-current="page">
                <?php echo htmlspecialchars($project['project_name']); ?> Dashboard
            </li>
        </ol>
    </nav>
    <?php if ($budget_alert): ?>
        <div class="alert <?php echo $budget_alert; ?>" role="alert">
            <strong>Budget Alert:</strong> You are approaching or have exceeded your budget.
        </div>
    <?php endif; ?>


    <script>
        // Wait for the page to load
        document.addEventListener('DOMContentLoaded', function () {
            // Check if the alert exists
            const alert = document.querySelector('.alert');
            if (alert) {
                // Add the 'show' class to trigger the animation
                alert.classList.add('show');

                // Set a timer to hide the alert after 5 seconds
                setTimeout(function () {
                    alert.classList.remove('show');
                }, 5000); // 5000ms = 5 seconds
            }
        });
    </script>


    <div class="container main-content">
        <h2><?php echo htmlspecialchars($project['project_name']); ?> Dashboard</h2>
        <p class="lead"><?php echo htmlspecialchars($project['description']); ?></p>

        <!-- Financial Summary -->
        <div class="mt-5">
            <h3>Financial Summary</h3>
            <div class="pie-chart-container">
                <canvas id="expensePieChart" height="200"></canvas>
            </div>
        </div>

        <!-- Category Breakdown Graph -->
        <div class="mt-5">
            <h3>Category-wise Expense Breakdown</h3>
            <div class="pie-chart-container">
                <canvas id="categoryBarChart" height="200"></canvas>
            </div>
        </div>



        <!-- Budget Update Section -->
        <div class="budget-section">
            <h4 style="font-size: 18px;">Existing Budget: ₹<?php echo number_format($project['budget'], 2); ?></h4>
            <h4 style="font-size: 18px;">Remaining Budget: ₹<?php echo number_format($remaining_budget, 2); ?></h4>
            <form action="" method="POST">
                <div class="mb-3">
                    <label for="new_budget" class="form-label">Update Budget</label>
                    <input type="number" class="form-control" id="new_budget" name="new_budget" value="" required>
                </div>
                <?php if (isset($budget_error)): ?>
                    <div class="alert alert-danger" role="alert"><?php echo $budget_error; ?></div>
                <?php endif; ?>
                <button type="submit" name="update_budget" class="btn btn-primary">Update</button>
            </form>
        </div>




        <!-- Expense Form -->
        <h3 class="mt-5">Add New Expense</h3>
        <form method="POST">
            <div class="mb-3">
                <div class="mb-3">
                    <label for="category" class="form-label">Category</label>
                    <select class="form-select input-field" id="category" name="category" required>
                        <option value="" selected disabled>Select a Category</option>
                        <option value="Labor">Labor</option>
                        <option value="Materials">Materials</option>
                        <option value="Transportation">Transportation</option>
                        <option value="Miscellaneous">Miscellaneous</option>
                    </select>
                </div>
                <label for="expense_name" class="form-label">Expense Name</label>
                <input type="text" name="expense_name" id="expense_name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="amount" class="form-label">Amount</label>
                <input type="number" name="amount" id="amount" class="form-control" required>
            </div>

            <button type="submit" name="add_expense" class="btn btn-primary">Add Expense</button>
        </form>
        <?php if (isset($expense_error)): ?>
            <div class="alert alert-danger mt-2"><?php echo $expense_error; ?></div>
        <?php endif; ?>

        <!-- Expenses List -->
        <h3 class="mt-5">Expenses List</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Expense Name</th>
                    <th>Category</th>
                    <th>Amount</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($expenses as $expense): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($expense['expense_name']); ?></td>
                        <td><?php echo htmlspecialchars($expense['category']); ?></td>
                        <td>₹<?php echo number_format($expense['amount'], 2); ?></td>
                        <td>
                            <a href="edit_expense.php?expense_id=<?php echo $expense['id']; ?>"
                                class="btn btn-warning btn-sm">Edit</a>
                            <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal"
                                data-expense-id="<?php echo $expense['id']; ?>">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>


    </div>
    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this expense?
                </div>
                <div class="modal-footer">
                    <form action="delete_expense.php" method="POST">
                        <input type="hidden" name="expense_id" id="deleteExpenseId">
                        <input type="hidden" name="project_id" value="<?php echo $project_id; ?>">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Error Modal -->
    <?php if ($delete_error): ?>
        <div class="modal fade show" id="deleteErrorModal" tabindex="-1" aria-labelledby="deleteErrorModalLabel"
            aria-hidden="true" style="display: block;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteErrorModalLabel">Error</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <?php echo $delete_error; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <script>
            var deleteErrorModal = new bootstrap.Modal(document.getElementById('deleteErrorModal'));
            deleteErrorModal.show();
        </script>
    <?php endif; ?>

    <!-- JavaScript for Modal -->
    <script>
        var deleteModal = document.getElementById('deleteModal');
        deleteModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var expenseId = button.getAttribute('data-expense-id');
            var deleteExpenseIdInput = document.getElementById('deleteExpenseId');
            deleteExpenseIdInput.value = expenseId;
        });
    </script>
    <script>
        // Pie Chart for Expense Breakdown
        var ctx1 = document.getElementById('expensePieChart').getContext('2d');
        var expensePieChart = new Chart(ctx1, {
            type: 'pie',
            data: {
                labels: ['Total Expenses', 'Remaining Budget'],
                datasets: [{
                    data: [<?php echo $total_expense; ?>, <?php echo $remaining_budget; ?>],
                    backgroundColor: ['#ff5733', '#28a745']
                }]
            }
        });

        // Bar Chart for Category Breakdown
        var ctx2 = document.getElementById('categoryBarChart').getContext('2d');
        var categoryBarChart = new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($category_expenses, 'category')); ?>,
                datasets: [{
                    label: 'Expense by Category',
                    data: <?php echo json_encode(array_column($category_expenses, 'category_total')); ?>,
                    backgroundColor: '#ffcc00'
                }]
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js"></script>

</body>

</html>