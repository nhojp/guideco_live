<?php
session_start();
include 'conn.php';
include 'head.php';
include 'guard-nav.php';

// Check if user is logged in and is a guard
if (!isset($_SESSION['loggedin']) || !isset($_SESSION['guard'])) {
    header('Location: index.php'); // Redirect if not logged in or not a guard
    exit;
}

$role = 'guard';
$role_id = $_SESSION['guard_id'] ?? null;

if ($role && $role_id) {
    $table_name = 'guards';
    $sql = "SELECT * FROM $table_name WHERE id = $role_id";
    $result = $conn->query($sql);

    if ($result && $result->num_rows == 1) {
        // Fetch guard details
        $user = $result->fetch_assoc();
        $_SESSION['edit_user'] = $user; // Store guard details in session for editing

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
            $birthdate = $_POST['birthdate'] ?? '';
            $sex = $_POST['sex'] ?? '';
            $contact_number = $_POST['contact_number'] ?? '';
            $address = $_POST['address'] ?? '';

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
        $error_message = "Guard data not found.";
    }
} else {
    $error_message = "Guard ID not set in session.";
}
?>

    <style>
    * {
        font-family: 'Poppins', sans-serif;
    }
</style>
<main class="flex-fill mt-5">
    <div class="container mt-4">
        <div class="container-fluid mt-2 mb-5">
            <div class="container-fluid bg-white mt-2 rounded-lg pb-2 border">
                <div class="row pt-3">
                    <div class="col-md-6">
                        <div class="container-fluid p-2">
                            <h3><strong>Edit Guard Credentials</strong></h3>
                        </div>
                    </div>
                </div>

                <?php
                // Display error or success messages
                if (!empty($error_message) || !empty($success_message)) {
                ?>
                    <div class="alert <?php echo !empty($success_message) ? 'alert-success' : 'alert-danger'; ?> mt-4" role="alert">
                        <?php echo !empty($success_message) ? $success_message : $error_message; ?>
                    </div>
                <?php
                }
                ?>

                <div class="container bg-white p-4 rounded-lg">
                    <?php if (isset($_SESSION['edit_user'])) {
                        $user = $_SESSION['edit_user'];
                    ?>
                        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                            <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                            <div class="form-row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="username"><strong>Username</strong></label>
                                        <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="password"><strong>Password</strong></label>
                                        <input type="password" class="form-control" id="password" name="password" value="<?php echo htmlspecialchars($user['password']); ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="first_name"><strong>First Name</strong></label>
                                        <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="middle_name"><strong>Middle Name</strong></label>
                                        <input type="text" class="form-control" id="middle_name" name="middle_name" value="<?php echo htmlspecialchars($user['middle_name']); ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="last_name"><strong>Last Name</strong></label>
                                        <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email"><strong>Email</strong></label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="position"><strong>Position</strong></label>
                                        <input type="text" class="form-control" id="position" name="position" value="<?php echo htmlspecialchars($user['position']); ?>" readonly>
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
                                        <select class="form-control" id="sex" name="sex">
                                            <option value="Male" <?php echo $user['sex'] == 'Male' ? 'selected' : ''; ?>>Male</option>
                                            <option value="Female" <?php echo $user['sex'] == 'Female' ? 'selected' : ''; ?>>Female</option>
                                        </select>
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
                                        <input type="text" class="form-control" id="address" name="address" value="<?php echo htmlspecialchars($user['address']); ?>">
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-success" name="update">Save</button>
                        </form>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'footer.php'; ?>
