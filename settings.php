<?php
session_start();
ob_start();

if (!isset($_SESSION['loggedin']) || !isset($_SESSION['admin'])) {
    header('Location: index.php'); // Redirect if not logged in or not admin
    exit;
}

include "head.php";
include "sidebar.php";
include "conn.php";

// Check role-specific access
if (isset($_SESSION['admin'])) {
    $role = 'admin';
    $role_id = $_SESSION['admin_id'] ?? null;
} elseif (isset($_SESSION['teacher'])) {
    $role = 'teacher';
    $role_id = $_SESSION['teacher_id'] ?? null;
} elseif (isset($_SESSION['guard'])) {
    $role = 'guard';
    $role_id = $_SESSION['guard_id'] ?? null;
} else {
    // Redirect to appropriate login page if role is not recognized
    header('Location: index.php');
    exit;
}

if ($role && $role_id) {
    $table_name = ($role == 'admin') ? 'admin' : (($role == 'teacher') ? 'teachers' : 'guards');
    $sql = "SELECT * FROM $table_name WHERE id = $role_id";
    $result = $conn->query($sql);

    if ($result && $result->num_rows == 1) {
        // Fetch user details
        $user = $result->fetch_assoc();
        $_SESSION['edit_user'] = $user; // Store user details in session for editing

        // Handle form submission to update credentials
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
            // Retrieve form data
            $id = $_POST['id'];
            $username = $_POST['username'];
            $password = $_POST['password']; // Note: This should be hashed before storing in real-world applications
            $first_name = $_POST['first_name'];
            $middle_name = $_POST['middle_name'];
            $last_name = $_POST['last_name'];
            $email = $_POST['email'];
            $position = $_POST['position'];
            $birthdate = isset($_POST['birthdate']) ? $_POST['birthdate'] : ''; // Initialize $birthdate with default value or empty string
            $sex = isset($_POST['sex']) ? $_POST['sex'] : ''; // Initialize $sex with default value or empty string
            $contact_number = isset($_POST['contact_number']) ? $_POST['contact_number'] : ''; // Initialize $contact_number with default value or empty string
            $address = isset($_POST['address']) ? $_POST['address'] : ''; // Initialize $address with default value or empty string

            // Update query
            $update_sql = "UPDATE $table_name SET username = '$username', password = '$password', 
                           first_name = '$first_name', middle_name = '$middle_name', 
                           last_name = '$last_name', email = '$email', position = '$position', 
                           birthdate = '$birthdate', sex = '$sex', contact_number = '$contact_number',
                           address = '$address'
                           WHERE id = $id";

            if ($conn->query($update_sql) === TRUE) {
                $success_message = "Credentials updated successfully.";
                // Update session with new data
                $user['username'] = $username;
                $user['first_name'] = $first_name;
                $user['middle_name'] = $middle_name;
                $user['last_name'] = $last_name;
                $user['email'] = $email;
                $user['position'] = $position;
                $user['birthdate'] = $birthdate;
                $user['sex'] = $sex;
                $user['contact_number'] = $contact_number;
                $user['address'] = $address;
                $_SESSION['edit_user'] = $user;
            } else {
                $error_message = "Error updating credentials: " . $conn->error;
            }
        }
    } else {
        // Handle case where user data is not found
        $error_message = ucfirst($role) . " data not found.";
    }
} else {
    // Handle case where role or role_id is not set in session
    $error_message = ucfirst($role) . " ID not set in session.";
}
?>
<div id="main">

    <?php include "header.php"; ?>
    <div class="container-fluid">
        <div class="container-fluid bg-white mt-2 rounded-lg border mb-2 pb-2">
            <div class="row pt-3">
                <div class="col-md-6">
                    <div class="container-fluid p-2">
                        <h3><strong>Edit <?php echo ucfirst($role); ?> Credentials</strong></h3>
                    </div>
                </div>

                <div class="container bg-white p-4 rounded-lg">
                    <?php
                    // Display error or success messages
                    if (!empty($error_message)) {
                        echo '<div class="alert alert-danger">' . $error_message . '</div>';
                    }
                    if (!empty($success_message)) {
                        echo '<div class="alert alert-success">' . $success_message . '</div>';
                    }

                    // Display user details in editable form
                    if (isset($_SESSION['edit_user'])) {
                        $user = $_SESSION['edit_user'];
                    ?>
                        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                            <input type="hidden" name="id" value="<?php echo $user['id']; ?>">


                            <div class="form-row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="first_name"><strong>First Name</strong></label>
                                        <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo ucwords($user['first_name']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="middle_name"><strong>Middle Name</strong></label>
                                        <input type="text" class="form-control" id="middle_name" name="middle_name" value="<?php echo ucwords($user['middle_name']); ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="last_name"><strong>Last Name</strong></label>
                                        <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo ucwords($user['last_name']); ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="username"><strong>Username</strong></label>
                                        <input type="text" class="form-control" id="username" name="username" value="<?php echo $user['username']; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="password"><strong>Password</strong></label>
                                        <input type="password" class="form-control" id="password" name="password" value="<?php echo $user['password']; ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email"><strong>Email</strong></label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?php echo $user['email']; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="position"><strong>Position</strong></label>
                                        <input type="text" class="form-control" id="position" name="position" value="<?php echo ucwords($user['position']); ?>" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="birthdate"><strong>Birthdate</strong></label>
                                        <input type="date" class="form-control" id="birthdate" name="birthdate" value="<?php echo htmlspecialchars($user['birthdate']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="sex"><strong>Sex</strong></label>
                                        <div class="form-group">
                                            <select class="form-control" id="sex" name="sex">
                                                <option value="Male" <?php if ($user['sex'] == 'Male') echo 'selected'; ?>>Male</option>
                                                <option value="Female" <?php if ($user['sex'] == 'Female') echo 'selected'; ?>>Female</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="contact_number"><strong>Contact Number</strong></label>
                                        <input type="text" class="form-control" id="contact_number" name="contact_number" value="<?php echo htmlspecialchars($user['contact_number']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="address"><strong>Address</strong></label>
                                        <input type="text" class="form-control" id="address" name="address" value="<?php echo ucwords(htmlspecialchars($user['address'])); ?>">
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-outline-success" name="update" style="width: 100%; margin: 5px">Save</button>
                        </form>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>

</div>

<?php
include "toast.php";
include "footer.php";
?>