<style>
    /* Custom CSS to align navbar items to the left */
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
</style>
<nav class="navbar navbar-expand-lg navbar-dark bg-success fixed-top">
    <div class="container">
        <!-- Logo -->
        <a class="navbar-brand d-flex align-items-center" href="student-index.php">
            <img src="img/guideco_logo.png" alt="Logo" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;">
            <span class="ml-2"><strong>GuideCo<sup class="small-text">Principal</sup></strong></span>
        </a>
        <!-- Toggler Button for Mobile View -->
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="principal.php">Dashboard</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="complaintsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        My Account
                    </a>
                    <div class="dropdown-menu" aria-labelledby="complaintsDropdown">
                        <a class="dropdown-item" href="principal-credentials.php">Profile</a>
                        <a class="dropdown-item" href="logout.php">Logout</a>
                        <!-- Add more dropdown items as needed -->
                    </div>
                </li>
            </ul>
        </div>
    </div>
</nav>
