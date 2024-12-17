<?php
include 'conn.php';
include 'head.php';
include 'principal-nav.php';

session_start();
if (!isset($_SESSION['loggedin']) || !isset($_SESSION['principal'])) {
    header('Location: index.php'); // Redirect if not logged in
    exit;
}

$principal_id = $_SESSION['principal']; // Assuming principal ID is stored in session

$sql = "SELECT * FROM principal WHERE id = $principal_id";
$result = $conn->query($sql);

if ($result && $result->num_rows == 1) {
    // Fetch principal details
    $principal = $result->fetch_assoc();
    $_SESSION['edit_user'] = $principal; // Store principal details in session for editing

    // Handle form submission to update credentials
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
        // Retrieve form data
        $id = $_POST['id'];
        $username = $_POST['username'];
        $password = $_POST['password']; // Note: This should be hashed before storing in real-world applications
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $email = $_POST['email'];
        $contact_number = $_POST['contact_number'];

        // Update query
        $update_sql = "UPDATE principal SET username = '$username', password = '$password', 
                       first_name = '$first_name', last_name = '$last_name', 
                       email = '$email', contact_number = '$contact_number'
                       WHERE id = $id";

        if ($conn->query($update_sql) === TRUE) {
            $success_message = "Credentials updated successfully.";
            // Update session with new data
            $principal['username'] = $username;
            $principal['first_name'] = $first_name;
            $principal['last_name'] = $last_name;
            $principal['email'] = $email;
            $principal['contact_number'] = $contact_number;
            $_SESSION['edit_user'] = $principal;
        } else {
            $error_message = "Error updating credentials: " . $conn->error;
        }
    }
} else {
    // Handle case where principal data is not found
    $error_message = "Principal data not found.";
}
?>
<main class="flex-fill mt-5">
<div class="container mt-4">
<div class="container-fluid mb-5">
    <div class="container-fluid bg-white mt-2 rounded-lg border">
        <div class="row pt-3">
            <div class="col-md-6">
                <div class="container-fluid p-2">
                    <h3><strong>Edit Principal Credentials</strong></h3>
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

                // Display principal details in editable form
                if (isset($_SESSION['edit_user'])) {
                    $principal = $_SESSION['edit_user'];
                ?>
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <input type="hidden" name="id" value="<?php echo $principal['id']; ?>">
                        <div class="form-row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="username"><strong>Username</strong></label>
                                    <input type="text" class="form-control" id="username" name="username" value="<?php echo $principal['username']; ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password"><strong>Password</strong></label>
                                    <input type="password" class="form-control" id="password" name="password" value="<?php echo $principal['password']; ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="first_name"><strong>First Name</strong></label>
                                    <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo ucwords($principal['first_name']); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="last_name"><strong>Last Name</strong></label>
                                    <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo ucwords($principal['last_name']); ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email"><strong>Email</strong></label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo $principal['email']; ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="contact_number"><strong>Contact Number</strong></label>
                                    <input type="text" class="form-control" id="contact_number" name="contact_number" value="<?php echo htmlspecialchars($principal['contact_number']); ?>" required>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success" name="update">Save</button>
                    </form>
                <?php } ?>
            </div>
        </div>
    </div>
</div></div></main>
<?php include 'footer.php'?>