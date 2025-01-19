<?php 
// Display error messages passed as GET parameters
$error = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - TrackBuild</title>
    <link rel="icon" href="../assets/logo.jpg" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: rgb(3, 24, 45); /* Dark background */
        }

        .login-container {
            margin-top: 5%;
            max-width: 90%;
            padding: 30px;
            background-color: #ffffff; /* White background for form */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .login-container h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        /* Media Queries for Responsive Design */
        @media (min-width: 576px) {
            .login-container {
                max-width: 400px;
            }
        }

        @media (min-width: 768px) {
            .login-container {
                padding: 40px;
            }

            .login-container h2 {
                font-size: 2rem;
            }

            .form-label {
                font-size: 1rem;
            }

            .btn {
                padding: 10px 20px;
                font-size: 1rem;
            }
        }

        @media (min-width: 992px) {
            .login-container {
                padding: 50px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container mx-auto">
            <h2>Login</h2>

            <!-- Display Alert if Error Exists -->
            <?php if ($error): ?>
                <div class="alert alert-danger text-center" role="alert">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form action="../controllers/login_handler.php" method="POST">
                <!-- Username or Email Field -->
                <div class="mb-3">
                    <label for="username_or_email" class="form-label">Username or Email</label>
                    <input type="text" class="form-control" id="username_or_email" name="username_or_email" placeholder="Enter your username or email" required>
                </div>

                <!-- Password Field -->
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>

            <!-- Signup Link -->
            <p class="mt-3 text-center">
                Don't have an account? <a href="signup.php">Sign up here</a>
            </p>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
