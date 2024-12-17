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

// Function to fetch all personnel data
function getAllPersonnelData($conn)
{
    $sql = "SELECT id, first_name, last_name, CONCAT(first_name, ' ', last_name) AS full_name, 'Guard' AS position FROM guards";
    $result = mysqli_query($conn, $sql);
    $personnelData = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $personnelData[] = $row;
    }
    return $personnelData;
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_personnel'])) {
    $personnelId = intval($_POST['delete_personnel']);

    $deleteSql = "DELETE FROM guards WHERE id = ?";

    $stmt = mysqli_prepare($conn, $deleteSql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $personnelId);
        $success = mysqli_stmt_execute($stmt);

        if ($success) {
            $deleteSuccess = true;
        } else {
            $errorMessage = 'Error deleting personnel record: ' . mysqli_stmt_error($stmt);
        }
        mysqli_stmt_close($stmt);
    } else {
        $errorMessage = 'Error preparing delete statement: ' . mysqli_error($conn);
    }
}

// Add Guard functionality
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['guard_first_name']) && isset($_POST['guard_last_name'])) {
    $guardFirstName = $_POST['guard_first_name'];
    $guardLastName = $_POST['guard_last_name'];
    $guardUsername = $_POST['guard_username'];
    $guardPassword = $_POST['guard_password'];

    // Check if username already exists
    $checkUsernameSql = "SELECT id FROM guards WHERE username = '$guardUsername'";
    $result = mysqli_query($conn, $checkUsernameSql);
    if (mysqli_num_rows($result) > 0) {
        $errorMessage = 'Error adding guard: Username already exists.';
    } else {
        $addGuardSql = "INSERT INTO guards (first_name, last_name, username, position, password)
                        VALUES ('$guardFirstName', '$guardLastName', '$guardUsername', 'Guard', '$guardPassword')";

        if (mysqli_query($conn, $addGuardSql)) {
            $_SESSION['add_guard_success'] = true;
            $addGuardSuccess = true;
        } else {
            $errorMessage = 'Error adding guard: ' . mysqli_error($conn);
        }
    }
}

// Update Guard functionality
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_guard_first_name']) && isset($_POST['edit_guard_last_name'])) {
    $guardId = intval($_POST['guard_id']);
    $editGuardFirstName = $_POST['edit_guard_first_name'];
    $editGuardLastName = $_POST['edit_guard_last_name'];

    $updateGuardSql = "UPDATE guards SET first_name = ?, last_name = ? WHERE id = ?";

    $stmt = mysqli_prepare($conn, $updateGuardSql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'ssi', $editGuardFirstName, $editGuardLastName, $guardId);
        $success = mysqli_stmt_execute($stmt);

        if ($success) {
            $addGuardSuccess = true; // Using the same flag for success messages
        } else {
            $errorMessage = 'Error updating guard: ' . mysqli_stmt_error($stmt);
        }
        mysqli_stmt_close($stmt);
    } else {
        $errorMessage = 'Error preparing update statement: ' . mysqli_error($conn);
    }
}

// Clear session variables after displaying messages
unset($_SESSION['add_guard_success']);

// Fetching data
$personnelData = getAllPersonnelData($conn);
?>
<div id="main">

    <?php include "header.php"; ?>
    <div class="container-fluid">
        <div class="container-fluid bg-white mt-2 rounded-lg border">
            <div class="row pt-3">
                <div class="col-md-6">
                    <div class="container-fluid p-2">
                        <h3><strong>School Guards</strong></h3>
                    </div>
                </div>
                <div class="col-md-5">
                    <input class="form-control" type="text" id="searchInput" placeholder="Search a name or position...">
                </div>
                <div class="col-md-1">
                    <div class="btn-group">
                        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#addGuardModal">
                            Add
                        </button>
                    </div>
                </div>
            </div>

            <table class="table table-hover table-bordered mt-2 text-center">
                <thead>
                    <tr>
                        <th style="width:35%;">Full Name</th>
                        <th style="width:10%;" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="personnelTable">
                    <?php foreach ($personnelData as $person) : ?>
                        <tr>
                            <td><?php echo ucwords(strtolower($person['full_name'])); ?></td>

                            <td class="text-center">
                                <button type="button" class="btn btn-outline-success" data-toggle="modal" data-target="#editModal<?php echo $person['id']; ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger" data-toggle="modal" data-target="#deleteModal<?php echo $person['id']; ?>" data-id="<?php echo $person['id']; ?>">
                                    <i class="fas fa-trash-alt"></i>
                                </button>

                                <!-- Delete Modal -->
                                <div class="modal fade" id="deleteModal<?php echo $person['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel<?php echo $person['id']; ?>" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header text-white bg-success">
                                                <h5 class="modal-title" id="deleteModalLabel<?php echo $person['id']; ?>">Delete Record</h5>
                                                <button type="button" class="btn-danger btn btn btn-circle" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                Are you sure you want to delete this record?
                                            </div>
                                            <div class="modal-footer">
                                                <form method="POST" action="">
                                                    <input type="hidden" name="delete_personnel" value="<?php echo $person['id']; ?>">
                                                    <button type="submit" class="btn btn-danger">Delete</button>
                                                    <button type="button" class="btn btn-outline-success" data-dismiss="modal">Cancel</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Edit Modal -->
                                <div class="modal fade" id="editModal<?php echo $person['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="editModalLabel<?php echo $person['id']; ?>" aria-hidden="true">
                                    <div class="modal-dialog modal-custom" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header text-white bg-success">
                                                <h5 class="modal-title" id="editModalLabel<?php echo $person['id']; ?>">Edit Guard</h5>
                                                <button type="button" class="btn-danger btn btn btn-circle" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body text-left">
                                                <form method="POST" action="">
                                                    <input type="hidden" name="guard_id" value="<?php echo $person['id']; ?>">
                                                    <div class="form-group">
                                                        <label for="edit_guard_first_name">First Name</label>
                                                        <input type="text" class="form-control" name="edit_guard_first_name" id="edit_guard_first_name" value="<?php echo $person['first_name']; ?>" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="edit_guard_last_name">Last Name</label>
                                                        <input type="text" class="form-control" name="edit_guard_last_name" id="edit_guard_last_name" value="<?php echo $person['last_name']; ?>" required>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-outline-danger" data-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-outline-success">Save changes</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <!-- Add Guard Modal -->
        <div class="modal fade" id="addGuardModal" tabindex="-1" role="dialog" aria-labelledby="addGuardModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-custom" role="document">
                <div class="modal-content">
                    <div class="modal-header text-white bg-success">
                        <h5 class="modal-title" id="addGuardModalLabel">Add Guard</h5>
                        <button type="button" class="btn-danger btn btn btn-circle" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form method="POST" action="">
                            <div class="form-group">
                                <label for="guard_first_name">First Name</label>
                                <input type="text" class="form-control" name="guard_first_name" id="guard_first_name" required>
                            </div>
                            <div class="form-group">
                                <label for="guard_last_name">Last Name</label>
                                <input type="text" class="form-control" name="guard_last_name" id="guard_last_name" required>
                            </div>
                            <div class="form-group">
                                <label for="guard_username">Username</label>
                                <input type="text" class="form-control" name="guard_username" id="guard_username" required>
                            </div>
                            <div class="form-group">
                                <label for="guard_password">Password</label>
                                <input type="password" class="form-control" name="guard_password" id="guard_password" required>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-outline-success">Add Guard</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    // Search filter
    document.getElementById('searchInput').addEventListener('keyup', function() {
        var value = this.value.toLowerCase();
        var rows = document.querySelectorAll('#personnelTable tr');

        rows.forEach(function(row) {
            var fullName = row.cells[0].textContent.toLowerCase();
            row.style.display = fullName.includes(value) ? '' : 'none';
        });
    });
</script>
<?php
include "toast.php";
include "footer.php";
?>