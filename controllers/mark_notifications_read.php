<?php
session_start();
require_once '../config/db.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Get the request data
$data = json_decode(file_get_contents('php://input'), true);

// If the 'mark all as read' action is triggered
if (isset($data['mark_all'])) {
    try {
        // Update all notifications for the user to "read" status
        $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        // Respond with success
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        // Handle errors
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>
