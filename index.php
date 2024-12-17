<?php
// Start the session
session_start();
ob_start();

// Include database connection
include "conn.php";

// Check if there's a toast message in the session
if (isset($_SESSION['toast_message']) && isset($_SESSION['toast_class'])) {
    $toast_message = $_SESSION['toast_message'];
    $toast_class = $_SESSION['toast_class'];

    // Unset the session variables so the message doesn't appear again
    unset($_SESSION['toast_message']);
    unset($_SESSION['toast_class']);
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get username and password from the form (sanitize inputs)
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // SQL query to fetch user details from the users table
    $sql_user = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    $result_user = $conn->query($sql_user);

    // SQL query to fetch user details from the admins table
    $sql_admin = "SELECT * FROM admin WHERE username = '$username' AND password = '$password'";
    $result_admin = $conn->query($sql_admin);

    // SQL query to fetch user details from the teachers table
    $sql_teacher = "SELECT * FROM teachers WHERE username = '$username' AND password = '$password'";
    $result_teacher = $conn->query($sql_teacher);

    // SQL query to fetch user details from the guards table
    $sql_guard = "SELECT * FROM guards WHERE username = '$username' AND password = '$password'";
    $result_guard = $conn->query($sql_guard);

    // SQL query to fetch user details from the principals table
    $sql_principal = "SELECT * FROM principal WHERE username = '$username' AND password = '$password'";
    $result_principal = $conn->query($sql_principal);

    if ($result_user && $result_user->num_rows == 1) {
        // User found in the users table
        $user = $result_user->fetch_assoc();

        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $username;
        $_SESSION['user'] = true;
        $_SESSION['user_id'] = $user['id']; // Save user id in session

        // Redirect to student-index.php or refresh the current page to display user info
        header("Location: student-index.php");
        exit;
    } elseif ($result_admin && $result_admin->num_rows == 1) {
        // Admin found in the admins table
        $admin = $result_admin->fetch_assoc();
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $username;
        $_SESSION['admin'] = true;
        $_SESSION['admin_id'] = $admin['id']; // Save admin id in session

        // Redirect to admin-index.php or refresh the current page to display admin info
        header("Location: dashboard.php");
        exit;
    } elseif ($result_teacher && $result_teacher->num_rows == 1) {
        // Teacher found in the teachers table
        $teacher = $result_teacher->fetch_assoc();
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $username;
        $_SESSION['teacher'] = true;
        $_SESSION['teacher_id'] = $teacher['id']; // Save teacher id in session

        // Redirect to teacher-index.php or refresh the current page to display teacher info
        header("Location: teacher-index.php");
        exit;
    } elseif ($result_guard && $result_guard->num_rows == 1) {
        // Guard found in the guards table
        $guard = $result_guard->fetch_assoc();
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $username;
        $_SESSION['guard'] = true;
        $_SESSION['guard_id'] = $guard['id']; // Save guard id in session

        // Redirect to guard-index.php or refresh the current page to display guard info
        header("Location: guard-index.php");
        exit;
    } elseif ($result_principal && $result_principal->num_rows == 1) {
        // Principal found in the principals table
        $principal = $result_principal->fetch_assoc();
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $username;
        $_SESSION['principal'] = true;
        $_SESSION['principal_id'] = $principal['id']; // Save principal id in session

        header("Location: principal.php");
        exit;
    } else {
        // Debug: Confirm query success
        error_log("Query executed successfully!");
        
        $_SESSION['toast_message'] = "Incorrect username or password!";
        $_SESSION['toast_class'] = "failed";  // Bootstrap success class
    
        // Redirect
        header("Location: index.php");
        exit();
    }

    // Close database connection
}

// Check if user is logged in and fetch user/admin details
if (isset($_SESSION['loggedin'])) {
    $user_id = $_SESSION['user_id'] ?? null;
    $admin_id = $_SESSION['admin_id'] ?? null;
    $teacher_id = $_SESSION['teacher_id'] ?? null;
    $guard_id = $_SESSION['guard_id'] ?? null;
    $principal_id = $_SESSION['principal_id'] ?? null;

    if ($user_id) {
        // Fetch user data
        $sql_fetch_user = "SELECT * FROM users WHERE id = $user_id";
        $result_fetch_user = $conn->query($sql_fetch_user);

        if ($result_fetch_user->num_rows == 1) {
            $row_user = $result_fetch_user->fetch_assoc();

            // Fetch student's first name and last name from students table
            $sql_fetch_student = "SELECT s.first_name, s.last_name 
                                  FROM students s
                                  WHERE s.user_id = $user_id";
            $result_fetch_student = $conn->query($sql_fetch_student);

            if ($result_fetch_student->num_rows == 1) {
                $row_student = $result_fetch_student->fetch_assoc();

                $first_name = $row_student['first_name'];
                $last_name = $row_student['last_name'];
            }
        }
    } elseif ($admin_id) {
        // Fetch admin data
        $sql_fetch_admin = "SELECT first_name, last_name, position FROM admin WHERE id = $admin_id";
        $result_fetch_admin = $conn->query($sql_fetch_admin);

        if ($result_fetch_admin->num_rows == 1) {
            $row_admin = $result_fetch_admin->fetch_assoc();
            $first_name = $row_admin['first_name'];
            $last_name = $row_admin['last_name'];
            $position = $row_admin['position'];
        }
    } elseif ($teacher_id) {
        // Fetch teacher data
        $sql_fetch_teacher = "SELECT * FROM teachers WHERE id = $teacher_id";
        $result_fetch_teacher = $conn->query($sql_fetch_teacher);

        if ($result_fetch_teacher->num_rows == 1) {
            $row_teacher = $result_fetch_teacher->fetch_assoc();
            $first_name = $row_teacher['first_name'];
            $last_name = $row_teacher['last_name'];
        }
    } elseif ($guard_id) {
        // Fetch guard data
        $sql_fetch_guard = "SELECT * FROM guards WHERE id = $guard_id";
        $result_fetch_guard = $conn->query($sql_fetch_guard);

        if ($result_fetch_guard->num_rows == 1) {
            $row_guard = $result_fetch_guard->fetch_assoc();
            $first_name = $row_guard['first_name'];
            $last_name = $row_guard['last_name'];
        }
    } elseif ($principal_id) {
        // Fetch principal data
        $sql_fetch_principal = "SELECT first_name, last_name FROM principal WHERE id = $principal_id";
        $result_fetch_principal = $conn->query($sql_fetch_principal);

        if ($result_fetch_principal->num_rows == 1) {
            $row_principal = $result_fetch_principal->fetch_assoc();
            $first_name = $row_principal['first_name'];
            $last_name = $row_principal['last_name'];
        }
    }
}
?>

<?php include "head.php"; ?>

<style>
    body {
        background: linear-gradient(to right, #2b7d2f 50%, #FFFFFF 50%);
        margin: 0;
        padding: 0;
        font-family: 'Montserrat', sans-serif;

    }

    .green-section {
        position: relative;
        background-image: url(img/school.jpg);
        background-size: cover;
        background-position: center;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        z-index: 1;
    }

    .green-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(43, 125, 47, 0.5);
        z-index: -1;
    }

    .green-section .content {
        color: #FFFFFF;
        z-index: 2;
    }

    .green-section h5 {
        margin-bottom: 20px;
        font-family: 'Montserrat', sans-serif;
        font-weight: 800;

    }

    .green-section h1 {
        margin-bottom: 20px;
        font-family: 'Montserrat', sans-serif;
        font-weight: 100;
    }

    .green-section .guide {
        color: #FFFFFF;
        font-family: 'Montserrat', sans-serif;
        font-weight: 900;
        font-size: 100px;
    }

    .green-section .co {
        color: #FFFFFF;
        font-family: 'Montserrat', sans-serif;
        font-weight: 900;
        font-size: 100px;
    }

    .green-section p {
        font-size: 1.5rem;
        margin-bottom: 40px;
        font-size: 15px;
    }

    .white-section {
        background-color: #f8f8ff;
        padding: 60px;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
    }

    .login-form {
        background-color: #FFFFFF;
        padding: 40px;
        border-radius: 8px;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 500px;
    }

    .login-form h2 {

        color: #1F5F1E;
        margin-bottom: 50px;
        text-align: center;
        font-weight: 800;
        font-size: 30px;
    }

    .form-group {
        margin-bottom: 20px;
        text-align: left;
    }

    .form-control {
        width: 100%;
        padding: 16px;
        font-size: 1.2rem;
        border: 1px solid #ccc;
        border-radius: 8px;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .form-control:focus {
        outline: none;
        border-color: #2b7d2f;
        box-shadow: 0 0 0 0.2rem rgba(43, 125, 47, 0.25);
    }

    .input-group-text {
        cursor: pointer;
    }

    .btn-primary {
        background-color: #1F5F1E;
        color: #FFFFFF;
        border-color: #2b7d2f;
        padding: 18px;
        font-size: 1.5rem;
        font-weight: bold;
        text-transform: uppercase;
        width: 100%;
        border-radius: 8px;
        transition: background-color 0.15s ease-in-out, border-color 0.15s ease-in-out;
    }

    .btn-primary:hover {
        background-color: #3d984a;
    }

    .alert-danger {
        margin-top: 20px;
    }

    .logo-container {
        position: absolute;
        bottom: 10px;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        justify-content: center;
        z-index: 2;
    }

    .logo-container img {
        width: 80px;
        height: auto;
        margin: 0 10px;
    }

    .logo-container .logo2 {
        width: 65px;
        height: 65px;
    }


    .green-section,
    .white-section {
        min-height: 100vh;
        position: relative;
    }

    .float-right {
        color: #1F5F1E;
    }

    .float-right:hover {
        color: #71C270;
    }



    @media (max-width: 720px) {
        body {
            background: #1F5F1E;
        }

        .login-form {
            padding: 20px;
        }

        .form-group {
            margin-bottom: 10px;
        }

        .login-form h2 {
            margin-bottom: 20px;
        }

        .white-section {
            padding: 40px 20px;
            margin: 0;
        }

        .green-section {
            display: none;
        }
    }
</style>

<body>

    <div class="container-fluid">
        <div class="row">
            <!-- Left Side - Green Section -->
            <div class="col-md-6 green-section">
                <div class="content">
                    <h5>Welcome to</h5>
                    <h1><span class="guide">Guide</span><span class="co">Co</span></h1>
                    <p>Your hub for expert guidance and counseling. Empower your journey to personal growth with our supportive insights and tools.</p>
                </div>

                <!-- Logos Section in Green Section -->
                <div class="logo-container">
                    <img src="img/guideco_logo1.png" alt="Logo 1">
                    <img src="img/superi.png" alt="Logo 3">
                    <img src="img/302878890_461445415997910_8949927714948066635_n-removebg-preview.png" alt="Logo 2" class="logo2">

                </div>
            </div>

            <!-- Right Side - White Section -->
            <div class="col-md-6 white-section d-flex align-items-center">
                <div class="login-form">
                    <h2>Login</h2>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" name="username" class="form-control" id="username" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <div class="input-group">
                                <input type="password" name="password" class="form-control" id="password" required>
                                <div class="input-group-append">
                                    <span class="input-group-text show-password" onclick="togglePassword()">
                                        <i class="fa fa-eye" id="eye-icon"></i>
                                    </span>
                                </div>
                            </div>
                            <small><a href="forgot-password.php" class="float-right mb-5">Forgot password?</a></small>
                        </div>
                        <div class="form-group">
                            <div class="g-recaptcha" data-sitekey="your-site-key"></div>
                        </div>
                        <button type="submit" class="btn btn-primary">Login</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</body>
<?php include "toast.php"; ?>
<?php include "footer.php"; ?>

<script>
    function togglePassword() {
        var passwordInput = document.getElementById('password');
        var eyeIcon = document.getElementById('eye-icon');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.classList.remove('fa-eye');
            eyeIcon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            eyeIcon.classList.remove('fa-eye-slash');
            eyeIcon.classList.add('fa-eye');
        }
    }
</script>