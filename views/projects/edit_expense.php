<?php  
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['expense_id'])) {
    header("Location: project_list.php");
    exit();
}

$expense_id = $_GET['expense_id'];
$user_id = $_SESSION['user_id'];
$budget_alert = ''; // Variable to hold alert message type
$notification_message = ''; // Variable to hold notification message

try {
    // Fetch expense details
    $stmt = $conn->prepare("SELECT * FROM expenses WHERE id = ? AND project_id IN (SELECT id FROM projects WHERE user_id = ?)");
    $stmt->bindValue(1, $expense_id, PDO::PARAM_INT);
    $stmt->bindValue(2, $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $expense = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$expense) {
        header("Location: engineer_project_list.php");
        exit();
    }

    // Fetch project budget
    $stmt = $conn->prepare("SELECT budget FROM projects WHERE id = ?");
    $stmt->bindValue(1, $expense['project_id'], PDO::PARAM_INT);
    $stmt->execute();
    $project = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$project) {
        header("Location: engineer_project_list.php");
        exit();
    }

    $allocated_budget = $project['budget'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $expense_name = $_POST['expense_name'];
        $amount = $_POST['amount'];
        $category = $_POST['category'];

        // Fetch updated total expenses for the project
        $stmt = $conn->prepare("SELECT SUM(amount) AS total_expenses FROM expenses WHERE project_id = ?");
        $stmt->bindValue(1, $expense['project_id'], PDO::PARAM_INT);
        $stmt->execute();
        $expenses = $stmt->fetch(PDO::FETCH_ASSOC);
        $total_expenses = $expenses['total_expenses'] ?? 0;

        // Check if the updated expense exceeds the budget
        if (($total_expenses - $expense['amount']) + $amount > $allocated_budget) {
            $budget_alert = 'alert-danger';
            $notification_message = "Updated total expenses exceed the project budget of ₹" . number_format($allocated_budget, 2);
        } else {
            // Update expense
            $stmt = $conn->prepare("UPDATE expenses SET expense_name = ?, amount = ?, category = ? WHERE id = ?");
            $stmt->bindValue(1, $expense_name, PDO::PARAM_STR);
            $stmt->bindValue(2, $amount, PDO::PARAM_STR); // Use PDO::PARAM_STR for decimal values
            $stmt->bindValue(3, $category, PDO::PARAM_STR);
            $stmt->bindValue(4, $expense_id, PDO::PARAM_INT);
            $stmt->execute();

            // Fetch updated total expenses for the project again after update
            $stmt = $conn->prepare("SELECT SUM(amount) AS total_expenses FROM expenses WHERE project_id = ?");
            $stmt->bindValue(1, $expense['project_id'], PDO::PARAM_INT);
            $stmt->execute();
            $expenses = $stmt->fetch(PDO::FETCH_ASSOC);
            $total_expenses = $expenses['total_expenses'] ?? 0;

            // Check if the total expenses exceed the budget after update
            if ($total_expenses > $allocated_budget) {
                $budget_alert = 'alert-danger';
                $notification_message = "Budget exceeded for project: {$project['project_name']}. Over by: ₹" . number_format($total_expenses - $allocated_budget, 2);
            } elseif ($total_expenses >= $allocated_budget * 0.8) {
                $budget_alert = 'alert-warning';
                $notification_message = "Budget is nearing the limit for project: {$project['project_name']}. ₹" . number_format($allocated_budget - $total_expenses, 2) . " remaining.";
            } else {
                $budget_alert = 'alert-success';
                $notification_message = "Expense updated successfully!";
            }

            // Redirect to project dashboard with success or failure message
            header("Location: project_dashboard.php?project_id=" . $expense['project_id'] . "&message=" . urlencode($notification_message));
            exit();
        }
    }
} catch (PDOException $e) {
    // Handle database errors
    error_log("Error in edit_expense.php: " . $e->getMessage());
    echo "An error occurred. Please try again later.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Expense - TrackBuild</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <!-- Display alert message if any -->
        <?php if ($budget_alert): ?>
            <div class="alert <?php echo $budget_alert; ?>" role="alert">
                <?php echo $notification_message; ?>
            </div>
        <?php endif; ?>

        <h2>Edit Expense</h2>
        <form action="edit_expense.php?expense_id=<?php echo $expense_id; ?>" method="POST">
            <div class="mb-3">
                <label for="expense_name" class="form-label">Expense Name</label>
                <input type="text" class="form-control" id="expense_name" name="expense_name" value="<?php echo htmlspecialchars($expense['expense_name']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="amount" class="form-label">Amount (INR)</label>
                <input type="number" class="form-control" id="amount" name="amount" value="<?php echo htmlspecialchars($expense['amount']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="category" class="form-label">Category</label>
                <select class="form-select" id="category" name="category" required>
                    <option value="Labor" <?php echo $expense['category'] == 'Labor' ? 'selected' : ''; ?>>Labor</option>
                    <option value="Materials" <?php echo $expense['category'] == 'Materials' ? 'selected' : ''; ?>>Materials</option>
                    <option value="Transportation" <?php echo $expense['category'] == 'Transportation' ? 'selected' : ''; ?>>Transportation</option>
                    <option value="Miscellaneous" <?php echo $expense['category'] == 'Miscellaneous' ? 'selected' : ''; ?>>Miscellaneous</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
    </div>
</body>
</html>
