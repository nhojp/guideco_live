<style>
    * {
        font-family: 'Poppins', sans-serif;
    }

    /* Custom CSS for student navbar */
    .navbar-nav {
        margin-left: auto;
        margin-right: auto;
    }

    .navbar-nav .nav-item {
        margin-left: 15px;
    }

    .navbar-nav .nav-item.active .nav-link {
        font-weight: bold;
    }

    .small-text {
        font-size: 0.5em;
        /* Adjust the size as needed */
    }
</style>

<nav class="navbar navbar-expand-lg navbar-dark bg-success fixed-top">
    <div class="container">
        <!-- Logo -->
        <a class="navbar-brand d-flex align-items-center" href="student-index.php">
            <img src="img/guideco_logo.png" alt="Logo" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;">
            <span class="ml-2"><strong>GuideCo<sup class="small-text">Student</sup></strong></span>
        </a>
        <!-- Toggler Button for Mobile View -->
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <!-- Navbar Links -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="student-index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="student-profile.php">Profile</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="student-recommender.php">Recommender</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
