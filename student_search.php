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

// Get the search query from the form
$search_query = isset($_GET['search_query']) ? trim($_GET['search_query']) : '';

// Pagination parameters
$limit = 100;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Build the WHERE clause for the search query
$where_sql = '';
$params = [];
$types = '';

if ($search_query) {
    // Use LIKE for partial matches in relevant fields
    $where_sql = "WHERE (u.username LIKE ? OR s.first_name LIKE ? OR s.last_name LIKE ?)";
    $search_param = '%' . $search_query . '%';
    $params = [$search_param, $search_param, $search_param];
    $types = 'sss';
}

// Get total count for pagination
$count_sql = "SELECT COUNT(*) FROM students s
            JOIN users u ON s.user_id = u.id
            JOIN section_assignment sa ON s.id = sa.student_id
            JOIN sections sec ON sa.section_id = sec.id
            JOIN strands st ON sec.strand_id = st.id
            JOIN teachers t ON sa.teacher_id = t.id
            $where_sql";

$count_stmt = $conn->prepare($count_sql);
if ($count_stmt) {
    if ($types) {
        $count_stmt->bind_param($types, ...$params);
    }
    $count_stmt->execute();
    $count_stmt->bind_result($total_records);
    $count_stmt->fetch();
    $count_stmt->close();
} else {
    throw new Exception("Prepare statement failed: " . $conn->error);
}

// Fetch students data based on the search query
$data_sql = "SELECT s.id, u.username, s.first_name, s.last_name, s.section_id,
                st.name AS strand_name, sec.section_name, sec.grade_level AS grade,
                CONCAT(sec.grade_level, ' - ', st.name, ' - ', sec.section_name) AS section_display,
                CONCAT(t.first_name, ' ', t.last_name) AS teacher_name,
                sa.school_year_id
            FROM students s
            JOIN users u ON s.user_id = u.id
            JOIN section_assignment sa ON s.id = sa.student_id
            JOIN sections sec ON sa.section_id = sec.id
            JOIN strands st ON sec.strand_id = st.id
            JOIN teachers t ON sa.teacher_id = t.id
            $where_sql
            ORDER BY s.id ASC
            LIMIT ? OFFSET ?";

$data_stmt = $conn->prepare($data_sql);
if ($data_stmt) {
    $types_with_limit = $types . 'ii';
    $params_with_limit = array_merge($params, [$limit, $offset]);
    $data_stmt->bind_param($types_with_limit, ...$params_with_limit);
    $data_stmt->execute();
    $data_result = $data_stmt->get_result();
    $students = $data_result->fetch_all(MYSQLI_ASSOC);
    $data_stmt->close();
} else {
    throw new Exception("Prepare statement failed: " . $conn->error);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle the edit functionality
    if (isset($_POST['edit_id'])) {
        $edit_id = $_POST['edit_id'];
        $first_name = htmlspecialchars($_POST['first_name']);
        $last_name = htmlspecialchars($_POST['last_name']);
        $section_id = $_POST['section_id'];

        // Update the student record
        $query = "UPDATE students SET first_name = ?, last_name = ?, section_id = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ssii', $first_name, $last_name, $section_id, $edit_id);

        if ($stmt->execute()) {
            // Trigger success modal
            echo '
                <script>
                    document.addEventListener("DOMContentLoaded", function () {
                        var successModal = new bootstrap.Modal(document.getElementById("successModal"));
                        successModal.show();
                        document.getElementById("closeSuccessModal").addEventListener("click", function () {
                            window.location.href = "admin-user-student.php";
                        });
                    });
                </script>';
        } else {
            // Trigger error modal
            echo '
                <script>
                    document.addEventListener("DOMContentLoaded", function () {
                        var errorModal = new bootstrap.Modal(document.getElementById("errorModal"));
                        errorModal.show();
                        
                    });
                </script>';
        }
    }

    // Handle the delete functionality
    if (isset($_POST['delete_id'])) {
        $delete_id = $_POST['delete_id'];

        // Get the user_id from the students table using the provided student ID
        $query = "SELECT user_id FROM students WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $delete_id);
        $stmt->execute();
        $stmt->bind_result($user_id);

        // Check if the student exists and fetch the result
        if ($stmt->fetch()) {
            // Close the result set
            $stmt->close();

            // Now delete the user record from the users table using the fetched user_id
            $deleteQuery = "DELETE FROM users WHERE id = ?";
            $deleteStmt = $conn->prepare($deleteQuery);
            $deleteStmt->bind_param('i', $user_id);

            if ($deleteStmt->execute()) {
                // Trigger success modal
                echo '
                <script>
                    document.addEventListener("DOMContentLoaded", function () {
                        var successModal = new bootstrap.Modal(document.getElementById("successModal"));
                        successModal.show();
                        document.getElementById("closeSuccessModal").addEventListener("click", function () {
                            window.location.href = "admin-user-student.php";
                        });
                    });
                </script>';
            } else {
                // Trigger error modal
                echo '
                <script>
                    document.addEventListener("DOMContentLoaded", function () {
                        var errorModal = new bootstrap.Modal(document.getElementById("errorModal"));
                        errorModal.show();
                        document.getElementById("closeSuccessModal").addEventListener("click", function () {
                            window.history.back();
                        });
                    });
                </script>';
            }
        } else {
            // Close the result set if no student found
            $stmt->close();

            // Trigger error modal if the student ID doesn't exist
            echo '
            <script>
                document.addEventListener("DOMContentLoaded", function () {
                    var errorModal = new bootstrap.Modal(document.getElementById("errorModal"));
                    errorModal.show();
                    document.getElementById("closeSuccessModal").addEventListener("click", function () {
                        window.history.back();
                    });
                });
            </script>';
        }
    }
}


// Fetch sections and strands for dropdowns
$sections_result = $conn->query("SELECT s.id, CONCAT(s.grade_level, ' - ', st.name, ' - ', s.section_name) AS section_display 
            FROM sections s 
            JOIN strands st ON s.strand_id = st.id
            ORDER BY st.name, s.section_name ASC");
$sections = $sections_result->fetch_all(MYSQLI_ASSOC);

$strands_result = $conn->query("SELECT id, name FROM strands ORDER BY name ASC");
$strands = $strands_result->fetch_all(MYSQLI_ASSOC);

$grades = [11, 12];

$school_years_result = $conn->query("SELECT id, CONCAT(year_start, ' - ', year_end) AS year_display FROM school_year ORDER BY year_start asc");
$school_years = $school_years_result->fetch_all(MYSQLI_ASSOC);

$teachers = $conn->query("SELECT * FROM teachers");
?>
<div id="main">

    <?php include "header.php"; ?>
    <div class="container-fluid">
        <div class="container-fluid bg-white mt-2 rounded-lg border">
            <div class="row pt-3">
                <div class="col-md-1">
                    <button class="btn btn-danger" onclick="window.location.href='student_page.php'">
                        <i class="fas fa-arrow-left"></i>
                    </button>
                </div>
                <div class="col-md-6">
                    <h2>Search Results for "<?php echo htmlspecialchars($search_query); ?>"</h2>
                </div>
                <div class="col-md-5">
                    <form action="admin-search.php" method="GET">
                        <input type="text" name="search_query" class="form-control" placeholder="Search Students" required>
                    </form>
                </div>
            </div>

            <div class="table-container">
                <table class="table table-hover mt-4 border text-center">
                    <thead style="background-color: #0C2D0B; color:white;">
                        <tr>
                            <th>Username</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Strand</th>
                            <th>Section</th>
                            <th>Grade</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="studentTable">
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['username']); ?></td>
                                <td><?php echo ucwords(htmlspecialchars($student['first_name'])); ?></td>
                                <td><?php echo ucwords(htmlspecialchars($student['last_name'])); ?></td>
                                <td><?php echo ucwords(htmlspecialchars($student['strand_name'])); ?></td>
                                <td><?php echo ucwords(htmlspecialchars($student['section_name'])); ?></td>
                                <td><?php echo htmlspecialchars($student['grade']); ?></td>
                                <td>
                                    <button class="btn btn-outline-success " onclick="viewStudent(<?php echo $student['id']; ?>)"><i class="fas fa-eye"></i></button>

                                    <!-- Edit Button -->
                                    <button type="button" class="btn btn-outline-warning " data-toggle="modal" data-target="#editModal<?php echo $student['id']; ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <!-- Delete Button -->
                                    <button type="button" class="btn btn-outline-danger " data-toggle="modal" data-target="#deleteModal<?php echo $student['id']; ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>

                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="row">
                <!-- Pagination logic -->
                <?php
                $total_pages = ceil($total_records / $limit);
                if ($total_pages > 1):
                ?>
                    <nav>
                        <ul class="pagination">
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                                    <a class="page-link" href="?search_query=<?php echo urlencode($search_query); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>

            <!-- Edit Modal -->
            <div class="modal fade modal-custom" id="editModal<?php echo $student['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="editModalLabel<?php echo $student['id']; ?>" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title" id="editModalLabel<?php echo $student['id']; ?>">Edit Student</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form method="POST" action="">
                                <input type="hidden" name="edit_id" value="<?php echo htmlspecialchars($student['id']); ?>">
                                <div class="form-group">
                                    <label for="edit_first_name<?php echo $student['id']; ?>">First Name:</label>
                                    <input type="text" id="edit_first_name<?php echo $student['id']; ?>" name="first_name" class="form-control" value="<?php echo ucwords(htmlspecialchars($student['first_name'])); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="edit_last_name<?php echo $student['id']; ?>">Last Name:</label>
                                    <input type="text" id="edit_last_name<?php echo $student['id']; ?>" name="last_name" class="form-control" value="<?php echo ucwords(htmlspecialchars($student['last_name'])); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="edit_section_id<?php echo $student['id']; ?>">Section:</label>
                                    <select id="edit_section_id<?php echo $student['id']; ?>" name="section_id" class="form-control" required>
                                        <?php foreach ($sections as $section): ?>
                                            <option value="<?php echo htmlspecialchars($section['id']); ?>" <?php echo $section['id'] == $student['section_id'] ? 'selected' : ''; ?>>
                                                <?php echo ucwords(htmlspecialchars($section['section_display'])); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-outline-success">Save Changes</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Delete Modal -->
            <div class="modal fade" id="deleteModal<?php echo $student['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel<?php echo $student['id']; ?>" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content shadow-lg rounded-3">
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title" id="deleteModalLabel<?php echo $student['id']; ?>">Delete Student</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body text-center">
                            <p>Are you sure you want to delete <strong><?php echo ucwords(htmlspecialchars($student['first_name'] . ' ' . $student['last_name'])); ?></strong>?</p>
                            <!-- You can add buttons inside the body if you want, for example -->
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                <form method="POST" action="">
                                    <input type="hidden" name="delete_id" value="<?php echo htmlspecialchars($student['id']); ?>">
                                    <button type="submit" class="btn btn-danger">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    function viewStudent(studentId) {
        // Redirect to the student detail view page
        window.location.href = 'student_profile.php?id=' + studentId; // Adjust the page URL as necessary
    }
</script>
<?php
include "toast.php";
include "footer.php";
?>