<?php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION['regenerated'])) {
    session_regenerate_id(true);
    $_SESSION['regenerated'] = true;
}

$error = '';

function insert_project($conn, $user_id, $project_name, $description, $land_area, $budget, $duration, $address, $file_path, $engineer_id, $client_id) {
    $stmt = $conn->prepare("INSERT INTO projects (user_id, project_name, description, land_area, budget, duration, address, document, engineer_id, client_id) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
    $stmt->bindValue(2, $project_name, PDO::PARAM_STR);
    $stmt->bindValue(3, $description, PDO::PARAM_STR);
    $stmt->bindValue(4, $land_area, PDO::PARAM_INT);
    $stmt->bindValue(5, $budget, PDO::PARAM_INT);
    $stmt->bindValue(6, $duration, PDO::PARAM_INT);
    $stmt->bindValue(7, $address, PDO::PARAM_STR);
    $stmt->bindValue(8, $file_path, PDO::PARAM_STR);
    $stmt->bindValue(9, $engineer_id, PDO::PARAM_INT);
    $stmt->bindValue(10, $client_id, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        return $conn->lastInsertId();
    } else {
        error_log("Error: " . $stmt->errorInfo());
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $project_name = htmlspecialchars($_POST['project_name']);
    $description = htmlspecialchars($_POST['description']);
    $land_area = $_POST['land_area'];
    $budget = $_POST['budget'];
    $duration = $_POST['duration'];
    $address = htmlspecialchars($_POST['address']);
    $upload_dir = '../../uploads/';
    $file_path = $upload_dir . basename($_FILES['document']['name']);
    $client_id = $_POST['client_id'];

    if ($land_area <= 0 || $budget <= 0) {
        $error = "Land area and budget must be positive numbers.";
    } elseif (isset($_FILES['document']) && $_FILES['document']['error'] == UPLOAD_ERR_OK) {
        $allowed_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        if (!in_array($_FILES['document']['type'], $allowed_types)) {
            $error = "Invalid file type. Only PDF and Word documents are allowed.";
        } elseif ($_FILES['document']['size'] > 5000000) {
            $error = "File size exceeds the maximum limit of 5MB.";
        } else {
            $file_name = basename($_FILES['document']['name']);
            $file_name = preg_replace('/[^a-zA-Z0-9\._-]/', '_', $file_name);
            $file_path = $upload_dir . $file_name;
            if (!move_uploaded_file($_FILES['document']['tmp_name'], $file_path)) {
                $error = "Failed to upload the document.";
            }
        }
    }

    if (!$error) {
        $project_id = insert_project($conn, $user_id, $project_name, $description, $land_area, $budget, $duration, $address, $file_path, $user_id, $client_id);
        if ($project_id) {
            $log_query = "INSERT INTO audit_logs (user_id, action, details) VALUES (?, 'Create Project', ?)";
            $log_stmt = $conn->prepare($log_query);
            $log_details = json_encode(['project_name' => $project_name]);
            $log_stmt->bindValue(1, $user_id, PDO::PARAM_INT);
            $log_stmt->bindValue(2, $log_details, PDO::PARAM_STR);
            $log_stmt->execute();

            $_SESSION['success'] = "Project created successfully!";
            header("Location: project_dashboard.php?project_id=$project_id");
            exit();
        } else {
            $error = "Failed to create the project. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Project - TrackBuild</title>
    <link rel="icon" href="../../assets/logo.jpg" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .home-icon { width: 40px; height: 40px; }
        .navbar-custom {
            background-color: #343a40;
            border-bottom: 1px solid #e0e0e0;
        }
        .navbar-dark .navbar-nav .nav-link {
            color: #fff;
        }
        .navbar-dark .navbar-nav .nav-link:hover {
            color: #ffcc00;
        }
        .btn-primary {
            background-color: #ffcc00;
            border-color: #e6b800;
        }
        .btn-primary:hover {
            background-color: #e6b800;
        }
        .container {
            margin-top: 50px;
        }
        .form-label {
            font-weight: bold;
        }
        .form-control:focus {
            border-color: #ffcc00;
            box-shadow: 0 0 5px rgba(255, 204, 0, 0.5);
        }
        .breadcrumb-item a:hover {
            text-decoration: underline;
        }
        .card {
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .alert {
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark"">
        <div class="container-fluid">
            <a class="navbar-brand" href="../engineer_dashboard.php">
                <img src="../../assets/home-icon.png" alt="Home" class="home-icon">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="../account/account_details.php">Account</a></li>
                    <li class="nav-item"><a class="nav-link" href="../projects/engineer_project_list.php">Projects</a></li>
                    <li class="nav-item"><a class="nav-link text-danger" href="../../logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="../engineer_dashboard.php">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Create Project</li>
        </ol>
    </nav>

    <div class="container">
        <div class="card p-4">
            <h2 class="mb-4">Create a New Project</h2>
            <p class="lead mb-4">Fill in the details of your new construction project below.</p>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <strong>Error!</strong> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form action="create_project.php" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                    <label for="client_id" class="form-label">Client</label>
                    <select class="form-control" id="client_id" name="client_id" required>
                        <option value="">Select a client</option>
                        <?php
                        $client_query = $conn->query("SELECT id, username FROM users WHERE role_name = 'client'");
                        while ($client = $client_query->fetch(PDO::FETCH_ASSOC)) {
                            echo '<option value="'.$client['id'].'">'.$client['username'].'</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="project_name" class="form-label">Project Name</label>
                    <input type="text" class="form-control" id="project_name" name="project_name" placeholder="Enter project name" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3" placeholder="Enter project description" required></textarea>
                </div>
                <div class="mb-3">
                    <label for="land_area" class="form-label">Land Area (sq ft)</label>
                    <input type="number" class="form-control" id="land_area" name="land_area" placeholder="Enter land area" required>
                </div>
                <div class="mb-3">
                    <label for="budget" class="form-label">Budget</label>
                    <input type="number" class="form-control" id="budget" name="budget" placeholder="Enter project budget" required>
                </div>
                <div class="mb-3">
                    <label for="duration" class="form-label">Duration (in months)</label>
                    <input type="number" class="form-control" id="duration" name="duration" placeholder="Enter project duration" required>
                </div>
                <div class="mb-3">
                    <label for="address" class="form-label">Project Address</label>
                    <input type="text" class="form-control" id="address" name="address" placeholder="Enter project address" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Create Project</button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
