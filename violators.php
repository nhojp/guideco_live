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

if (isset($_POST['student_id'])) {
    $delete_id = $_POST['student_id'];

    // SQL query to delete violation
    $delete_query = "DELETE FROM violations WHERE id = '$delete_id'";
    $delete_result = mysqli_query($conn, $delete_query);

    if (!$delete_result) {
        // Set error toast message
        $_SESSION['toast_message'] = "Deletion failed: " . mysqli_error($conn);
        $_SESSION['toast_class'] = "danger";  // Bootstrap danger class
    } else {
        // Set success toast message
        $_SESSION['toast_message'] = "Violation record deleted successfully!";
        $_SESSION['toast_class'] = "success";  // Bootstrap success class
    }

    // Redirect back to violators.php
    header("Location: violators.php");
    exit;
}

// Pagination parameters
$limit = 10; // Number of entries to show per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Get current page from URL or default to 1
$offset = ($page - 1) * $limit; // Calculate offset for SQL query

// SQL query to count total records
$total_query = "SELECT COUNT(*) as total FROM violations";
$total_result = mysqli_query($conn, $total_query);
$total_row = mysqli_fetch_assoc($total_result);
$totalEntries = $total_row['total']; // Get total number of entries
$totalPages = ceil($totalEntries / $limit); // Calculate total number of pages

// SQL query to fetch violations data with LIMIT and OFFSET
$violations_query = "
SELECT violations.id, students.id AS student_id, students.first_name, students.middle_name, students.last_name, 
students.age, students.sex, sections.section_name, sections.grade_level,
violation_list.violation_description AS violation, 
CASE
WHEN violations.guard_id IS NOT NULL THEN guards.first_name
WHEN violations.teacher_id IS NOT NULL THEN teachers.first_name
WHEN violations.admin_id IS NOT NULL THEN admin.first_name
ELSE 'Unknown'
END AS reported_by_name,
CASE
WHEN violations.guard_id IS NOT NULL THEN 'Guard'
WHEN violations.teacher_id IS NOT NULL THEN 'Teacher'
ELSE 'Unknown'
END AS reported_by_type,
violations.reported_at AS reportedAt
FROM violations
JOIN students ON violations.student_id = students.id
JOIN sections ON students.section_id = sections.id
LEFT JOIN guards ON violations.guard_id = guards.id
LEFT JOIN teachers ON violations.teacher_id = teachers.id
LEFT JOIN admin ON violations.admin_id = admin.id
JOIN violation_list ON violations.violation_id = violation_list.id
ORDER BY violations.reported_at DESC
LIMIT $limit OFFSET $offset
";

$violations_result = mysqli_query($conn, $violations_query);
if (!$violations_result) {
    die("Violations query failed: " . mysqli_error($conn));
}
?>
<style>
    .page-item.active .page-link {
        background-color: #155d14;
        border-color: #155d14;
    }

    .pagination-container {
        display: flex;
        justify-content: center;
        margin-top: 15px;
    }

    .pagination .page-item .page-link {
        width: 40px;
        height: 40px;
        padding: 0;
        text-align: center;
        line-height: 40px;
        border-radius: 50%;
        background-color: #0C2D0B;
        color: white;
        margin: 0 5px;
    }

    .pagination .page-item.active .page-link {
        background-color: #28a745;
        border-color: #28a745;
    }

    .pagination .page-item:hover .page-link {
        background-color: #218838;
        border-color: #218838;
    }
</style>
<div id="main">

    <?php include "header.php"; ?>
    <div class="container-fluid">
        <div class="container-fluid bg-white mt-2 rounded-lg border mb-2">
            <div class="row pt-3">
                <div class="col-md-6">
                    <div class="container-fluid p-2">
                        <h3><strong>Violators</strong></h3>
                    </div>
                </div>
                <div class="col-md-6">
                    <input class="form-control" type="text" id="searchInput" placeholder="Search violators">
                </div>
            </div>

            <div class="row pt-2 pb-2 filter-container align-items-center">
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="filter_grade">Filter by Grade:</label>
                        <select class="form-control" id="filter_grade">
                            <option value="">All Grades</option>
                            <option value="11">Grade 11</option>
                            <option value="12">Grade 12</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group">
                        <label for="filter_section">Filter by Section:</label>
                        <select class="form-control" id="filter_section">
                            <option value="">All Sections</option>
                            <?php
                            // Fetching sections for filter
                            $sections_query = "SELECT id, section_name FROM sections";
                            $sections_result = mysqli_query($conn, $sections_query);

                            if ($sections_result) {
                                while ($section_row = mysqli_fetch_assoc($sections_result)) {
                                    echo "<option value='" . htmlspecialchars($section_row['section_name']) . "'>" . ucwords(htmlspecialchars($section_row['section_name'])) . "</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label for="filter_reported_by_type">Reported By:</label>
                        <select class="form-control" id="filter_reported_by_type">
                            <option value="">All</option>
                            <option value="Teacher">Teacher</option>
                            <option value="Guard">Guard</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label for="filter_violation">Violation:</label>
                        <select class="form-control" id="filter_violation">
                            <option value="">All Violations</option>
                            <?php
                            // Fetching violations for filter
                            $violations_list_query = "SELECT DISTINCT violation_description FROM violation_list ORDER BY violation_description";
                            $violations_list_result = mysqli_query($conn, $violations_list_query);

                            if ($violations_list_result) {
                                while ($violation_row = mysqli_fetch_assoc($violations_list_result)) {
                                    echo "<option value='" . htmlspecialchars($violation_row['violation_description']) . "'>" . ucwords(htmlspecialchars($violation_row['violation_description'])) . "</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="col-md-1 d-flex align-items-center">
                    <a href="violator_report.php" class="btn btn-success mt-3 w-100">
                        Report
                    </a>
                </div>

                <div class="col-md-1 d-flex align-items-center">
                    <a href="violator_print.php" class="btn btn-outline-success mt-3 w-100">
                        <i class="fas fa-print fa-fw"></i> Print
                    </a>
                </div>
            </div>

            <!-- Display total entries message -->
            <div class="custom-message">
                <?php
                // Update this message based on the total entries
                if ($totalEntries > 0) {
                    $from = $offset + 1;
                    $to = min($offset + $limit, $totalEntries);
                    echo "Showing $from to $to of $totalEntries entries.";
                } else {
                    echo "No entries found.";
                }
                ?>
            </div>
            <div class="table-wrapper" style="position: relative; max-height: 250px; overflow-y: auto;">

                <table class="table table-hover table-bordered mt-2 text-center" id="violations_table">
                    <thead class="thead-custom">
                        <tr>
                            <th style="width:30%;">Name</th>
                            <th style="width:5%;">Grade</th>
                            <th style="width:20%;">Violation</th>
                            <th style="width:15%;">Reported by</th>
                            <th style="width:20%;">Reported at</th>
                            <th class="text-center" style="width:15%">Action</th>
                            <th style="display:none;">Reported by Type</th> <!-- Hidden column for filter -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($violations_result)) : ?>
                            <tr data-grade="<?php echo htmlspecialchars(strtolower($row['grade_level'] ?? $row['grade'])); ?>" data-section="<?php echo htmlspecialchars(strtolower($row['section_name'])); ?>" data-reported-by-type="<?php echo htmlspecialchars(strtolower($row['reported_by_type'])); ?>" data-violation="<?php echo htmlspecialchars(strtolower($row['violation'])); ?>">
                                <td><?php echo ucwords(htmlspecialchars($row['first_name'] . ' ' . $row['middle_name'] . ' ' . $row['last_name'])); ?></td>
                                <td><?php echo htmlspecialchars($row['grade_level']); ?></td>
                                <td><?php echo ucwords(htmlspecialchars($row['violation'])); ?></td>
                                <td><?php echo ucwords(htmlspecialchars($row['reported_by_name'])); ?></td>
                                <td><?php echo htmlspecialchars(date('F j, Y, g:i a', strtotime($row['reportedAt']))); ?></td>
                                <td class="text-center">
                                    <!-- Delete button with data-id attribute to identify the student -->
                                    <!-- Delete button with data-id attribute to identify the student -->
                                    <button class="btn btn-outline-danger" data-toggle="modal" data-target="#deleteModal" data-id="<?php echo htmlspecialchars($row['id']); ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                                <td style="display:none;"><?php echo htmlspecialchars($row['reported_by_type']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            Are you sure you want to delete the violation record of this student? This action cannot be undone.
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <!-- Deletion form -->
                            <form action="violators.php" method="POST" id="deleteForm">
                                <input type="hidden" name="student_id" id="student_id">
                                <button type="submit" class="btn btn-danger">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pagination-container">
                <ul class="pagination">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a href="?page=<?php echo $page - 1; ?>" class="page-link custom-page-link">«</a>
                        </li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a href="?page=<?php echo $i; ?>" class="page-link custom-page-link"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a href="?page=<?php echo $page + 1; ?>" class="page-link custom-page-link">»</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>

</div>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.getElementById('searchInput');
        const filterGrade = document.getElementById('filter_grade');
        const filterSection = document.getElementById('filter_section');
        const filterReportedByType = document.getElementById('filter_reported_by_type');
        const filterViolation = document.getElementById('filter_violation');
        const tableRows = document.querySelectorAll('#violations_table tbody tr');

        searchInput.addEventListener('keyup', filterTable);
        filterGrade.addEventListener('change', filterTable);
        filterSection.addEventListener('change', filterTable);
        filterReportedByType.addEventListener('change', filterTable);
        filterViolation.addEventListener('change', filterTable);

        function filterTable() {
            const searchValue = searchInput.value.toLowerCase();
            const gradeValue = filterGrade.value.toLowerCase();
            const sectionValue = filterSection.value.toLowerCase();
            const reportedByValue = filterReportedByType.value.toLowerCase();
            const violationValue = filterViolation.value.toLowerCase();

            tableRows.forEach(row => {
                const rowGrade = row.getAttribute('data-grade').toLowerCase();
                const rowSection = row.getAttribute('data-section').toLowerCase();
                const rowReportedByType = row.getAttribute('data-reported-by-type').toLowerCase();
                const rowViolation = row.getAttribute('data-violation').toLowerCase();
                const rowName = row.children[0].textContent.toLowerCase();

                const matchesSearch = rowName.includes(searchValue);
                const matchesGrade = !gradeValue || rowGrade === gradeValue;
                const matchesSection = !sectionValue || rowSection === sectionValue;
                const matchesReportedBy = !reportedByValue || rowReportedByType === reportedByValue;
                const matchesViolation = !violationValue || rowViolation === violationValue;

                if (matchesSearch && matchesGrade && matchesSection && matchesReportedBy && matchesViolation) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }


        // Listen for the modal opening to set the student ID dynamically
        $('#deleteModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget); // Button that triggered the modal
            var studentId = button.data('id'); // Extract the student ID from data-id attribute
            var modal = $(this);
            modal.find('#student_id').val(studentId); // Set the value of the hidden input to the student ID
        });


    });
</script>
<?php
include "toast.php";
include "footer.php";
?>