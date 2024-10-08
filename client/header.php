<!doctype html>
<html lang="en">
<head>
    <title>CAR PARKING</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link href='https://fonts.googleapis.com/css?family=Roboto:400,100,300,700' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">

    <style>
        body {
            font-family: 'Roboto', sans-serif;
        }
        .navbar {
            padding: 1rem 2rem;
        }
        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
        }
        .nav-link {
            font-size: 1.1rem;
            margin-right: 1rem;
        }
        .nav-link:hover {
            color: #023C6E;
        }
        .navbar-toggler {
            border: none;
        }
        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml;charset=utf8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3E%3Cpath stroke='rgba(0,0,0,.5)' stroke-width='2' d='M5 6h20M5 13h20M5 20h20'/%3E%3C/svg%3E");
        }
        .btn-logout {
            background-color: #dc3545;
            color: white;
            border: none;
            font-size: 1rem;
            font-weight: bold;
            margin-left: 1rem;
        }
        .btn-logout:hover {
            background-color: #c82333;
        }
        .social-media a {
            color: #023C6E;
            font-size: 1.2rem;
            margin: 0 0.5rem;
        }
        .social-media a:hover {
            color: #1d4ed8;
        }
        @media (max-width: 767px) {
            .navbar-brand {
                font-size: 1.2rem;
            }
            .nav-link {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
<!-- <section class="ftco-section" style="padding-top: 20px; padding-bottom: 10px;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 text-center mb-5">
                <h2 class="heading-section" >CAR PARKING</h2>
            </div>
        </div>
    </div>
</section> -->

<div >
    <nav class="navbar navbar-expand-lg ftco_navbar ftco-navbar-light" id="ftco-navbar">
        <a class="navbar-brand" href="user_dashboard.php">CAR PARKING</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#ftco-nav" aria-controls="ftco-nav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="ftco-nav">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item active"><a href="user_dashboard.php" class="nav-link">Home</a></li>
                <li class="nav-item"><a href="ongoing_booking.php" class="nav-link">OnGoing Bookings</a></li>
                <li class="nav-item"><a href="user_booking_status.php" class="nav-link">Bookings</a></li>
                <li class="nav-item"><a href="#" class="nav-link">Contact</a></li>
            </ul>
            <a href="../logout.php" class="btn btn-logout">Logout</a>
        </div>
    </nav>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
