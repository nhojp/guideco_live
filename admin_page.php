<?php
session_start();
ob_start();

// Check if there's a toast message in the session
if (isset($_SESSION['toast_message']) && isset($_SESSION['toast_class'])) {
    $toast_message = $_SESSION['toast_message'];
    $toast_class = $_SESSION['toast_class'];

    // Unset the session variables so the message doesn't appear again
    unset($_SESSION['toast_message']);
    unset($_SESSION['toast_class']);
}

if (!isset($_SESSION['loggedin']) || !isset($_SESSION['admin'])) {
    header('Location: index.php'); // Redirect if not logged in or not admin
    exit;
}

include "head.php";
include "sidebar.php";
include "conn.php";

function generateId($conn)
{
    $sql = "SELECT MAX(id) AS max_id FROM admin";
    $result = $conn->query($sql);
    if (!$result) {
        echo "Error executing query: " . $conn->error;
        return null;
    }

    $row = $result->fetch_assoc();
    $max_id = $row['max_id'];

    if ($max_id) {
        $generated_id = $max_id + 1; // Use numeric addition
    } else {
        $generated_id = 1;
    }

    return $generated_id;
}
$generated_id = generateId($conn);

// Function to fetch admins from the database
function fetchAdmins($conn)
{
    $sql = "SELECT * FROM admin";
    $result = $conn->query($sql);
    if (!$result) {
        echo "Error executing query: " . $conn->error;
        return [];
    }

    $admins = [];
    while ($row = $result->fetch_assoc()) {
        $admins[] = $row;
    }

    return $admins;
}

// Function to handle deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];

    $sql = "DELETE FROM admin WHERE id = '$delete_id'";
    if ($conn->query($sql)) {
        // Set success toast message
        $_SESSION['toast_message'] = "Admin deleted successfully.";
        $_SESSION['toast_class'] = "success";  // Bootstrap success class
    } else {
        // Set error toast message
        $_SESSION['toast_message'] = "Error deleting admin: " . $conn->error;
        $_SESSION['toast_class'] = "danger";  // Bootstrap danger class
    }

    // Redirect to the admin page
    header("Location: admin_page.php");
    exit;
}

// Function to handle editing
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_id'])) {
    $edit_id = $_POST['edit_id'];

    $position = $_POST['position'];

    $sql = "UPDATE admin SET position = '$position' WHERE id = '$edit_id'";
    if ($conn->query($sql)) {
        // Set success toast message
        $_SESSION['toast_message'] = "Admin updated successfully.";
        $_SESSION['toast_class'] = "success";  // Bootstrap success class    
    } else {
        // Set error toast message
        $_SESSION['toast_message'] = "Error updating admin: " . $conn->error;
        $_SESSION['toast_class'] = "danger";  // Bootstrap danger class    
    }
    header("Location: admin_page.php");
    exit;
}

// Function to handle adding an admin
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['edit_id'])) {
    // Retrieve form input values
    $username = isset($_POST['username']) ? $_POST['username'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $first_name = isset($_POST['first_name']) ? $_POST['first_name'] : '';
    $last_name = isset($_POST['last_name']) ? $_POST['last_name'] : '';
    $position = isset($_POST['position']) ? $_POST['position'] : '';

    // Check if all required fields are filled
    if ($username && $password && $first_name && $last_name) {
        // Use prepared statement to avoid SQL injection
        $stmt = $conn->prepare("INSERT INTO admin (id, username, password, first_name, last_name, position) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $generated_id, $username, $password, $first_name, $last_name, $position);

        try {
            if ($stmt->execute()) {
                // Set success toast message
                $_SESSION['toast_message'] = "Admin added successfully.";
                $_SESSION['toast_class'] = "success";  // Bootstrap success class    
            }
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) { // Duplicate entry error code
                // Set error toast message for duplicate username
                $_SESSION['toast_message'] = "Username already exists.";
                $_SESSION['toast_class'] = "danger";  // Bootstrap danger class
            } else {
                // Set error toast message for other errors
                $_SESSION['toast_message'] = "Error adding admin: " . $e->getMessage();
                $_SESSION['toast_class'] = "danger";  // Bootstrap danger class
            }
        }

        header("Location: admin_page.php");
        exit;
    } else {
        $errorMessage = "Please fill in all required fields.";
    }
}


$admins = fetchAdmins($conn);
?>
<div id="main">

    <?php include "header.php"; ?>
    <div class="container-fluid">
        <div class="container-fluid bg-white mt-2 rounded-lg border">
            <div class="row pt-3">
                <div class="col-md-6">
                    <div class="container-fluid p-2">
                        <h3><strong>Admin List</strong></h3>
                    </div>
                </div>
                <div class="col-md-6">
                    <input class="form-control" type="text" id="searchInput" placeholder="Search a name or position...">
                </div>
            </div>

            <div class="row pt-3 pb-3">
                <div class="col-md-12 text-right">
                    <div class="btn-group">
                        <button type="button" class="btn btn-success pr-4 pl-4" data-toggle="modal" data-target="#addAdminModal">
                            Add
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="table-container">
            <table class="table table-hover table-bordered mt-2 text-center">
                <thead>
                    <tr>
                        <th style="width:20%;">Full Name</th>
                        <th style="width:15%;">Position</th>
                        <th style="width:15%;">Email</th>
                        <th class="text-center" style="width:15%;">Actions</th>
                    </tr>
                </thead>
                <tbody id="adminTableBody">
                    <?php if (count($admins) > 0) : ?>
                        <?php foreach ($admins as $admin) : ?>
                            <tr>
                                <td><?php echo ucwords(htmlspecialchars($admin['first_name']))  . ' ' . ucwords(htmlspecialchars($admin['last_name'])); ?></td>
                                <td><?php echo ucwords(htmlspecialchars($admin['position'])); ?></td>
                                <td><?php echo htmlspecialchars($admin['email']); ?></td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-outline-success" data-toggle="modal" data-target="#editModal<?php echo urlencode($admin['id']); ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <button type="button" class="btn btn-outline-danger" data-toggle="modal" data-target="#deleteModal<?php echo htmlspecialchars($admin['id']); ?>">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="5">No admins found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <!-- Modal for Adding an Admin -->
        <div class="modal fade" id="addAdminModal" tabindex="-1" role="dialog" aria-labelledby="addAdminModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header text-white bg-success">
                        <h5 class="modal-title" id="addAdminModalLabel">Add Admin</h5>
                        <button type="button" class="btn-danger btn btn btn-circle" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form method="POST" action="">
                            <div class="form-group">
                                <input type="hidden" id="generated_id" name="generated_id" class="form-control" value="<?php echo htmlspecialchars($generated_id); ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label for="username">Username:</label>
                                <input type="text" id="username" name="username" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="password">Password:</label>
                                <input type="password" id="password" name="password" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="first_name">First Name:</label>
                                <input type="text" id="first_name" name="first_name" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="last_name">Last Name:</label>
                                <input type="text" id="last_name" name="last_name" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="position">Position:</label>
                                <input type="text" id="position" name="position" class="form-control">
                            </div>
                            <button type="submit" class="btn btn-success" style="width: 100%;">Add Admin</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal for Editing Admin -->
        <?php foreach ($admins as $admin) : ?>
            <div class="modal fade" id="editModal<?php echo urlencode($admin['id']); ?>" tabindex="-1" role="dialog" aria-labelledby="editModalLabel<?php echo urlencode($admin['id']); ?>" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header text-white bg-success">
                            <h5 class="modal-title" id="editModalLabel<?php echo urlencode($admin['id']); ?>">Edit Admin</h5>
                            <button type="button" class="btn-danger btn btn btn-circle" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form method="POST" action="">
                                <input type="hidden" name="edit_id" value="<?php echo htmlspecialchars($admin['id']); ?>">
                                <div class="form-group">
                                    <label for="position">Position:</label>
                                    <input type="text" id="position" name="position" class="form-control" value="<?php echo htmlspecialchars($admin['position']); ?>">
                                </div>
                                <button type="submit" class="btn btn-success" style="width: 100%;">Save</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Modal for Deleting Admin -->
        <?php foreach ($admins as $admin) : ?>
            <div class="modal fade" id="deleteModal<?php echo urlencode($admin['id']); ?>" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel<?php echo urlencode($admin['id']); ?>" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header text-white bg-success">
                            <h5 class="modal-title" id="deleteModalLabel<?php echo urlencode($admin['id']); ?>">Delete Admin</h5>
                            <button type="button" class="btn-danger btn btn btn-circle" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <p>Are you sure you want to delete this admin?</p>

                        </div>
                        <div class="modal-footer">
                            <form method="POST" action="">
                                <input type="hidden" name="delete_id" value="<?php echo htmlspecialchars($admin['id']); ?>">
                                <button type="submit" class="btn btn-danger" style="width: 100%;">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
</div>

<script>
    // Script to filter table based on search input
    document.getElementById('searchInput').addEventListener('input', function() {
        let searchValue = this.value.toLowerCase();
        let tableRows = document.querySelectorAll('#adminTableBody tr');
        tableRows.forEach(row => {
            let cells = row.querySelectorAll('td');
            let matches = Array.from(cells).some(cell => cell.textContent.toLowerCase().includes(searchValue));
            row.style.display = matches ? '' : 'none';
        });
    });
</script>
<?php
include "toast.php";
include "footer.php";
?>