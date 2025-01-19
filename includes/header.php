<?php
// Start session only if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>TrackBuild</title>
    <style>
        /* Base Styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }

        .navbar {
            margin-bottom: 20px;
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 600;
        }

        .nav-link {
            font-size: 1rem;
            color: #fff !important;
        }

        .nav-link:hover {
            color: #007bff !important;
        }

        /* Mobile-first Navigation Styles */
        @media (max-width: 576px) {
            .navbar-toggler {
                border-color: transparent;
            }

            .navbar-collapse {
                background-color: #343a40;
            }

            .navbar-nav {
                text-align: center;
            }

            .navbar-nav .nav-item {
                margin-bottom: 10px;
            }

            .navbar-nav .nav-link {
                font-size: 1.2rem;
            }
        }

        /* Tablet Screens */
        @media (min-width: 576px) and (max-width: 768px) {
            .navbar-nav .nav-link {
                font-size: 1.1rem;
            }
        }

        /* Desktop Screens */
        @media (min-width: 768px) {
            .navbar-nav .nav-link {
                font-size: 1rem;
            }

            .navbar-nav .nav-item {
                margin-left: 20px;
            }
        }

    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" href="../index.php">TrackBuild</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link" href="../views/projects/projects_list.php">Projects</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../views/account/account_details.php">Account</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../views/reports/report_dashboard.php">Reports</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../logout.php">Logout</a>
            </li>
        </ul>
    </div>
</nav>

<!-- Bootstrap JS and Dependencies -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
