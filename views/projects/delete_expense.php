<?php
session_start();
require_once '../../config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Check if required POST parameters are provided
if (!isset($_POST['expense_id']) || !isset($_POST['project_id'])) {
    header("Location: ../projects/projects_list.php");
    exit();
}

$expense_id = $_POST['expense_id'];
$project_id = $_POST['project_id'];
$user_id = $_SESSION['user_id'];

try {
    // Verify that the expense belongs to the project and the user
    $stmt = $conn->prepare("
        SELECT e.id 
        FROM expenses e 
        INNER JOIN projects p ON e.project_id = p.id 
        WHERE e.id = ? AND p.id = ? AND p.user_id = ?
    ");
    $stmt->bindValue(1, $expense_id, PDO::PARAM_INT);
    $stmt->bindValue(2, $project_id, PDO::PARAM_INT);
    $stmt->bindValue(3, $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $expense = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$expense) {
        // Expense does not exist or does not belong to the user
        header("Location: project_dashboard.php?project_id=$project_id&error=" . urlencode("Expense not found or unauthorized access."));
        exit();
    }

    // Delete the expense
    $stmt = $conn->prepare("DELETE FROM expenses WHERE id = ?");
    $stmt->bindValue(1, $expense_id, PDO::PARAM_INT);
    $stmt->execute();

    // Redirect back to the dashboard
    header("Location: project_dashboard.php?project_id=$project_id");
    exit();

} catch (PDOException $e) {
    // Redirect back to the dashboard with an error message
    $error_message = "Failed to delete expense. Please try again.";
    header("Location: project_dashboard.php?project_id=$project_id&error=" . urlencode($error_message));
    exit();
}
?>
