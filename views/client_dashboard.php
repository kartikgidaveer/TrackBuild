<?php
session_start();
require_once '../config/db.php';
require_once '../includes/role_helpers.php';

// Check if the user is logged in and has a valid role
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: ../views/login.php?error=" . urlencode("You must be logged in to access the dashboard."));
    exit();
}

// Redirect based on role
if ($_SESSION['role'] !== 'client') {
    header("Location: ../views/engineer_dashboard.php");
    exit();
}

// Fetch user details
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username FROM users WHERE id = :user_id");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch unread notifications for the logged-in user (client)
$stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC");
$stmt->bindValue(1, $user_id, PDO::PARAM_INT);
$stmt->execute();
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch projects assigned to the client
$projects_stmt = $conn->prepare("
    SELECT p.id, p.project_name, p.description, p.budget, 
           (SELECT COALESCE(SUM(e.amount), 0) FROM expenses e WHERE e.project_id = p.id) AS total_expenses
    FROM projects p
    JOIN project_access pa ON pa.project_id = p.id
    WHERE pa.user_id = :client_id
");
$projects_stmt->bindParam(':client_id', $user_id, PDO::PARAM_INT);
$projects_stmt->execute();
$projects = $projects_stmt->fetchAll(PDO::FETCH_ASSOC);

// Insert notification logic with duplication prevention
foreach ($projects as $project) {
    $total_expense = $project['total_expenses'];
    $budget_alert = '';
    if ($total_expense >= $project['budget']) {
        $budget_alert = 'alert-danger'; // Over budget
    } elseif ($total_expense >= 0.8 * $project['budget']) {
        $budget_alert = 'alert-warning'; // Approaching budget
    }

    if ($budget_alert) {
        $notification_message = $budget_alert === 'alert-danger'
            ? "Budget exceeded for project: {$project['project_name']}"
            : "Budget is nearing the limit for project: {$project['project_name']}";

        // Check if a similar notification already exists
        $check_stmt = $conn->prepare("
            SELECT COUNT(*) FROM notifications 
            WHERE user_id = ? AND project_id = ? AND message = ? AND is_read = 0
        ");
        $check_stmt->execute([$user_id, $project['id'], $notification_message]);
        $notification_exists = $check_stmt->fetchColumn();

        if (!$notification_exists) {
            // Insert new notification
            $insert_stmt = $conn->prepare("
                INSERT INTO notifications (user_id, project_id, message, is_read, created_at) 
                VALUES (?, ?, ?, 0, NOW())
            ");
            $insert_stmt->execute([$user_id, $project['id'], $notification_message]);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Dashboard - TrackBuild</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .home-icon {
            width: 40px;
            height: 40px;
        }

        .navbar-custom {
            background-color: #f0f8ff;
        }

        .budget-alert {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
        }

        .alert-warning {
            background-color: #fff3cd;
            color: #856404;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        .notification-read {
            background-color: #f0f0f0;
            font-weight: normal;
        }

        .notification-unread {
            font-weight: bold;
        }

        /* General Card Styling */
        .card {
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
            overflow: hidden;
        }

        /* Card Hover Effect */
        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        /* Card Titles */
        .card-title {
            font-size: 1.25rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }

        /* Card Text */
        .card-text {
            font-size: 1rem;
            color: #555;
            margin-bottom: 15px;
        }

        /* Button Styles */
        .card .btn {
            font-size: 0.9rem;
            font-weight: 600;
            padding: 10px 15px;
            border-radius: 5px;
            transition: background-color 0.2s ease-in-out, color 0.2s ease-in-out;
        }

        .card .btn-success {
            background-color: #28a745;
            border-color: #28a745;
        }

        .card .btn-success:hover {
            background-color: #218838;
            border-color: #1e7e34;
        }

        .card .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }

        .card .btn-primary:hover {
            background-color: #0056b3;
            border-color: #004085;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .card {
                margin-bottom: 20px;
            }
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="client_dashboard.php">
                <img src="../assets/home-icon.png" alt="Home" class="home-icon">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="account/client_account_details.php">Account</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="projects/client_project_list.php">Projects</a>
                    </li>
                    <li class="nav-item">
                        <button class="btn btn-warning" id="openNotificationsModal">
                            Notifications <span class="badge badge-light"
                                id="notificationCount"><?= count($notifications) ?></span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="../logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="client_dashboard.php">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
        </ol>
    </nav>

    <!-- Main Content -->
    <div class="container mt-5">
        <h2>Welcome to Your Dashboard, <?php echo htmlspecialchars($user['username']); ?>!</h2>
        <p class="lead">Track the progress of your construction projects with ease.</p>
        <hr>

        <!-- Budget Alerts -->
        <?php foreach ($projects as $project): ?>
            <?php
            $total_expense = $project['total_expenses'];
            $budget_alert = '';
            if ($total_expense >= $project['budget']) {
                $budget_alert = 'alert-danger'; // Over budget
            } elseif ($total_expense >= 0.8 * $project['budget']) {
                $budget_alert = 'alert-warning'; // Approaching budget
            }
            ?>
            <?php if ($budget_alert): ?>
                <div class="budget-alert <?php echo $budget_alert; ?>">
                    <strong><?php echo htmlspecialchars($project['project_name']); ?>:</strong>
                    <?php echo $total_expense >= $project['budget'] ? "Over budget!" : "Approaching budget."; ?>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>

        <!-- Cards -->
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">View Project Overview</h5>
                        <p class="card-text">Access a detailed summary of your projects, track expenses, and monitor budget usage to stay informed and in control.</p>

                        <a href="projects/client_project_list.php" class="btn btn-success">View Projects</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card mb-4 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Download Reports</h5>
                        <p class="card-text">Generate and download detailed reports for your projects, including
                            expenses and budget summaries.</p>
                        <a href="reports/client_report_dashboard.php" class="btn btn-primary">Generate Report</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include('../includes/footer.php'); ?>

    <!-- Notifications Modal -->
    <div class="modal fade" id="notificationsModal" tabindex="-1" aria-labelledby="notificationsModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="notificationsModalLabel">Notifications</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body notification-box" id="notificationsContainer">
                    <?php if (count($notifications) > 0): ?>
                        <?php foreach ($notifications as $notification): ?>
                            <a class="d-block notification-item <?= $notification['is_read'] ? 'notification-read' : 'notification-unread' ?>"
                                data-id="<?= $notification['id'] ?>">
                                <?= htmlspecialchars($notification['message']) ?>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-center">No new notifications</p>
                    <?php endif; ?>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" id="markAllAsReadBtn" <?= count($notifications) === 0 ? 'disabled' : '' ?>>Mark All as Read</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mark all notifications as read
        document.getElementById('markAllAsReadBtn').addEventListener('click', function () {
            fetch('../controllers/mark_notifications_read.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ mark_all: true })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove all notification items from the modal and show "No new notifications"
                        document.querySelector('#notificationsContainer').innerHTML = '<p class="text-center">No new notifications</p>';

                        // Update notification count to 0
                        document.getElementById('notificationCount').innerText = '0';

                        // Close the modal
                        $('#notificationsModal').modal('hide');
                    }
                });
        });

        $(document).ready(function () {
            // Open Notifications Modal
            $('#openNotificationsModal').click(function () {
                $('#notificationsModal').modal('show');
            });

            // Mark notifications as read
            $('.notification-item').click(function () {
                const notificationId = $(this).data('id');
                $.post('../controllers/mark_notifications_read.php', { id: notificationId }, function (response) {
                    if (response.success) {
                        $(`#notification_${notificationId}`).addClass('notification-read');
                        updateNotificationCount();
                    }
                });
            });

            // Update notification count
            function updateNotificationCount() {
                const count = $('.notification-unread').length;
                $('#notificationCount').text(count);
            }
        });

    </script>
</body>

</html>