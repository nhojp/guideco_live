<?php
session_start();

if (!isset($_SESSION['loggedin']) || !isset($_SESSION['admin'])) {
    header('Location: index.php'); // Redirect if not logged in or not admin
    exit;
}
include "head.php";
include "sidebar.php";
include "conn.php";


// Handle teacher creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_teacher'])) {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Hash the password for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Prepare and execute the SQL statement for inserting a teacher
    $stmt = $conn->prepare("INSERT INTO teachers (first_name, last_name, username, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $first_name, $last_name, $username, $hashed_password);

    // Execute the statement
    if ($stmt->execute()) {
        echo "<div class='success-message'>Teacher added successfully!</div>";
    } else {
        echo "<div class='error-message'>Error adding teacher: " . $stmt->error . "</div>";
    }

    $stmt->close(); // Close the statement
}

// Handle teacher update (from modal)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_teacher'])) {
    $update_id = $_POST['update_id'];
    $update_first_name = $_POST['update_first_name'];
    $update_last_name = $_POST['update_last_name'];
    $update_username = $_POST['update_username'];

    $update_stmt = $conn->prepare("UPDATE teachers SET first_name = ?, last_name = ?, username = ? WHERE id = ?");
    $update_stmt->bind_param("sssi", $update_first_name, $update_last_name, $update_username, $update_id);

    if ($update_stmt->execute()) {
        echo "<div class='success-message'>Teacher updated successfully!</div>";
    } else {
        echo "<div class='error-message'>Error updating teacher: " . $update_stmt->error . "</div>";
    }

    $update_stmt->close();
}

// Handle teacher deletion
if (isset($_POST['delete_teacher'])) {
    $delete_id = $_POST['teacher_id'];
    $delete_stmt = $conn->prepare("DELETE FROM teachers WHERE id = ?");
    $delete_stmt->bind_param("i", $delete_id);

    if ($delete_stmt->execute()) {
        echo "<div class='success-message'>Teacher deleted successfully!</div>";
    } else {
        echo "<div class='error-message'>Error deleting teacher: " . $delete_stmt->error . "</div>";
    }

    $delete_stmt->close();
}

// Fetch teachers from the database
$result = $conn->query("SELECT * FROM teachers");
$teachers = $result->fetch_all(MYSQLI_ASSOC);
?>
<div id="main">

    <?php include "header.php"; ?>
    <div class="container-fluid">
        <div class="container-fluid bg-white mt-2 rounded-lg border mb-2">
            <div class="row pt-3">
                <div class="col-md-6">
                    <div class="container-fluid p-2">
                        <h3><strong>Teachers List</strong></h3>
                    </div>
                </div>
                <div class="col-md-6">
                    <input class="form-control" type="text" id="searchInput" placeholder="Search a name or position...">
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 text-right mb-2">
                    <div class="btn-group">
                        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#addTeacherModal">
                            <i class="fas fa-plus"></i> Add
                        </button>
                    </div>
                </div>
            </div>

            <div class="table-wrapper" style="position: relative; max-height: 400px; overflow-y: auto;">
                <table class="table table-hover mt-4 border text-center table-bordered">
                    <thead>
                        <tr>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <?php foreach ($teachers as $teacher): ?>
                            <tr>
                                <td><?php echo ucwords(htmlspecialchars($teacher['first_name'])); ?></td>
                                <td><?php echo ucwords(htmlspecialchars($teacher['last_name'])); ?></td>
                                <td>
                                    <!-- Edit Button -->
                                    <button class="btn btn-outline-success edit-btn"
                                        data-id="<?php echo $teacher['id']; ?>"
                                        data-first_name="<?php echo htmlspecialchars(ucwords($teacher['first_name'])); ?>"
                                        data-last_name="<?php echo htmlspecialchars(ucwords($teacher['last_name'])); ?>"
                                        data-username="<?php echo htmlspecialchars($teacher['username']); ?>"
                                        data-toggle="modal"
                                        data-target="#editTeacherModal"
                                        data-label-first="First Name"
                                        data-label-last="Last Name"
                                        data-label-username="Username">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <!-- Delete Button -->
                                    <button class="btn btn-outline-danger delete-btn"
                                        data-id="<?php echo $teacher['id']; ?>"
                                        data-toggle="modal"
                                        data-target="#confirmDeleteModal">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Teacher Modal -->
    <div class="modal fade" id="addTeacherModal" tabindex="-1" role="dialog" aria-labelledby="addTeacherModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header text-white bg-success">
                    <h5 class="modal-title" id="addTeacherModalLabel">Add Teacher</h5>
                    <button type="button" class="btn btn-danger" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form method="POST" class="teacher-form">
                        <input type="hidden" name="add_teacher" value="1">
                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <input type="text" name="first_name" id="first_name" placeholder="First Name" required class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <input type="text" name="last_name" id="last_name" placeholder="Last Name" required class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" name="username" id="username" placeholder="Username" required class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" name="password" id="password" placeholder="Password" required class="form-control">
                        </div>

                        <button type="submit" class="btn btn-outline-success" style="width: 100%;">Add Teacher</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Teacher Modal -->
    <div class="modal fade" id="editTeacherModal" tabindex="-1" role="dialog" aria-labelledby="editTeacherModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header text-white bg-success">
                    <h5 class="modal-title" id="editTeacherModalLabel">Edit Teacher</h5>
                    <button type="button" class="btn btn-danger" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form method="POST" class="teacher-form">
                        <input type="hidden" name="update_teacher" value="1">
                        <input type="hidden" name="update_id" id="update_teacher_id">
                        <div class="form-group">
                            <label for="update_first_name">First Name</label>
                            <input type="text" name="update_first_name" id="update_first_name" placeholder="First Name" required class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="update_last_name">Last Name</label>
                            <input type="text" name="update_last_name" id="update_last_name" placeholder="Last Name" required class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="update_username">Username</label>
                            <input type="text" name="update_username" id="update_username" placeholder="Username" required class="form-control">
                        </div>

                        <button type="submit" class="btn btn-outline-success" style="width: 100%;">Update Teacher</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirm Delete Modal -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header text-white bg-success">
                    <h5 class="modal-title" id="confirmDeleteModalLabel">Confirm Deletion</h5>
                    <button type="button" class="btn btn-danger" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this teacher?
                </div>
                <div class="modal-footer">
                    <!-- Form for deletion -->
                    <form id="deleteTeacherForm" method="POST">
                        <input type="hidden" name="delete_teacher" value="1">
                        <input type="hidden" id="delete_teacher_id" name="teacher_id">
                        <button type="submit" class="btn btn-danger">Yes, delete</button>
                    </form>
                    <button type="button" class="btn btn-outline-success" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Script to populate the edit modal with current teacher data
        document.querySelectorAll('.edit-btn').forEach(function(button) {
            button.addEventListener('click', function() {
                document.getElementById('update_teacher_id').value = this.getAttribute('data-id');
                document.getElementById('update_first_name').value = this.getAttribute('data-first_name');
                document.getElementById('update_last_name').value = this.getAttribute('data-last_name');
                document.getElementById('update_username').value = this.getAttribute('data-username');
            });
        });

        // Script for delete confirmation
        document.querySelectorAll('.delete-btn').forEach(function(button) {
            button.addEventListener('click', function() {
                var teacherId = this.getAttribute('data-id');
                document.getElementById('delete_teacher_id').value = teacherId;
            });
        });
    </script>
</div>
<script>
    // Search functionality
    document.getElementById('searchInput').addEventListener('input', function() {
        const filter = this.value.toLowerCase(); // Get the input value and convert to lowercase
        const rows = document.querySelectorAll('#tableBody tr'); // Get all rows in the table body

        rows.forEach(row => {
            const cells = row.querySelectorAll('td'); // Get all cells in the row
            const match = Array.from(cells).some(cell => cell.textContent.toLowerCase().includes(filter));
            row.style.display = match ? '' : 'none'; // Show or hide the row based on the match
        });
    });
</script>
<?php
include "toast.php";
include "footer.php";
?>