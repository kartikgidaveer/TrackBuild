<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to TrackBuild</title>
    <link rel="icon" href="assets/logo.jpg" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    @import url(https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap);.feature-card,.features h2,footer{text-align:center}footer,footer a:hover{color:#fff}body{margin:0;font-family:Poppins,sans-serif;scroll-behavior:smooth;background-color:#f4f4f8;color:#333}.fixed-header{position:fixed;top:0;left:0;width:100%;z-index:1000;background:linear-gradient(to right,#000428,#004e92);color:#fff;padding:15px 30px;box-shadow:0 4px 6px rgba(0,0,0,.1);display:flex;justify-content:space-between;align-items:center}.fixed-header h5{font-size:1.8rem;font-weight:600}.fixed-header .btn{margin:0 10px;border-radius:25px;padding:10px 20px;transition:background-color .3s,transform .3s}.fixed-header .btn:hover{background-color:#007bff;transform:scale(1.05)}.hero{margin-top:80px}.carousel-inner img{max-width:100%;height:auto;filter:brightness(.6)}.carousel-caption{background:rgba(255,255,255,.9);padding:30px;border-radius:15px;box-shadow:0 8px 16px rgba(0,0,0,.2);color:#333}.learn-more-btn{margin-top:20px;padding:10px 30px;border-radius:25px}.features{padding:60px 20px;background:#fff}.features h2{margin-bottom:40px;font-weight:600;color:#333;font-size:1.5rem}.feature-card{padding:20px;background:#fff;border-radius:15px;box-shadow:0 8px 16px rgba(0,0,0,.1);transition:transform .3s,box-shadow .3s;margin-bottom:20px}.feature-card:hover{transform:translateY(-10px);box-shadow:0 16px 32px rgba(0,0,0,.2)}footer{background-color:#343a40;padding:20px 0;box-shadow:0 -4px 6px rgba(0,0,0,.1)}footer a{color:#ccc;text-decoration:none}@media (min-width:768px){.fixed-header{padding:15px 50px}.features{padding:80px 40px}.features h2{font-size:2rem}.feature-card{padding:40px}}@media (min-width:992px){.hero{margin-top:100px}.features{padding:100px 60px}.feature-card{margin-bottom:40px}.carousel-caption{padding:40px}.learn-more-btn{padding:15px 40px}}
    </style>
</head>
<body>

    <!-- Fixed Header -->
    <div class="fixed-header">
        <div>
            <h5 class="mb-0">TrackBuild</h5>
        </div>
        <div class="hamburger d-md-none" onclick="toggleMenu()">☰</div>
        <ul id="nav-menu" class="d-none d-md-flex">
            <a href="views/login.php" class="btn btn-outline-light">Login</a>
            <a href="views/signup.php" class="btn btn-warning">Signup</a>
        </ul>
    </div>

    <!-- Hero Section -->
    <section class="hero">
        <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img src="assets/main.jpg" class="d-block w-100" alt="TrackBuild Hero 1">
                    <div class="carousel-caption">
                        <h1>Welcome to TrackBuild</h1>
                        <p>Track and manage your construction projects effortlessly.</p>
                        <a href="#features" class="btn btn-primary learn-more-btn">Learn More</a>
                    </div>
                </div>
                <div class="carousel-item">
                    <img src="assets/hero2.jpg" class="d-block w-100" alt="TrackBuild Hero 2">
                    <div class="carousel-caption">
                        <h1>Real-Time Budget Tracking</h1>
                        <p>Stay on top of your expenses with live updates.</p>
                        <a href="#features" class="btn btn-primary learn-more-btn">Learn More</a>
                    </div>
                </div>
                <div class="carousel-item">
                    <img src="assets/hero3.jpg" class="d-block w-100" alt="TrackBuild Hero 3">
                    <div class="carousel-caption">
                        <h1>Budget Overrun Alerts</h1>
                        <p>Receive instant notifications if your budget exceeds limits.</p>
                        <a href="#features" class="btn btn-primary learn-more-btn">Learn More</a>
                    </div>
                </div>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features">
        <div class="container">
            <h2>Why Choose TrackBuild?</h2>
            <div class="row">
                <div class="col-md-4">
                    <div class="feature-card">
                        <h4>Real-Time Tracking</h4>
                        <p>Monitor your project's budget and expenses in real-time.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <h4>Budget Overrun Alerts</h4>
                        <p>Stay informed with notifications if your budget exceeds the limit.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <h4>Secure Platform</h4>
                        <p>Your data is protected with advanced security protocols.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <p>&copy; 2024 TrackBuild. All Rights Reserved.</p>
        <ul class="list-inline">
            <li class="list-inline-item"><a href="#">Privacy Policy</a></li>
            <li class="list-inline-item"><a href="#">Terms of Service</a></li>
        </ul>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleMenu() {
            const navMenu = document.getElementById('nav-menu');
            navMenu.classList.toggle('d-none');
        }
    </script>
</body>
</html>
