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

// Get the selected student ID and other parameters from the URL
$student_id = isset($_GET['student_id']) ? intval($_GET['student_id']) : 0;

// Fetch details of the selected student
$sql_selected_student = "SELECT s.id, s.first_name, s.last_name, sec.section_name, sec.grade_level
                         FROM students s
                         INNER JOIN sections sec ON s.section_id = sec.id
                         WHERE s.id = ?";
$stmt = $conn->prepare($sql_selected_student);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result_selected_student = $stmt->get_result();
$selected_student = $result_selected_student->fetch_assoc();

// Check if selected student exists
if (!$selected_student) {
    echo "Error: No student found with ID $student_id.";
    exit;
}

// Fetch all students for victim selection
$sql_all_students = "SELECT s.id, s.first_name, s.last_name, sec.section_name, sec.grade_level
                     FROM students s
                     INNER JOIN sections sec ON s.section_id = sec.id";
$result_all_students = $conn->query($sql_all_students);

// Check for query error
if ($result_all_students === false) {
    echo "Error: " . $conn->error;
    exit;
}

?>
<div id="main">

    <?php include "header.php"; ?>
    <div class="container-fluid">
        <div class="container-fluid bg-white mt-2 rounded-lg border">
            <div class="row pt-3">
                <div class="col-md-6">
                    <div class="container-fluid p-2">
                        <h4>
                            <strong>Victim of <span class="text-danger">
                                    <?php echo htmlspecialchars(ucwords($selected_student['first_name']) . " " . ucwords($selected_student['last_name']) . " of " . ucwords($selected_student['grade_level']) . " - " . ucwords($selected_student['section_name'])); ?>
                                </span></strong>
                        </h4>
                    </div>
                </div>
                <div class="col-md-6">
                    <input class="form-control" type="text" id="searchInput" placeholder="Search student">
                </div>
            </div>

            <table class="table table-hover table-bordered mt-2 text-center">
                <thead>
                    <tr>
                        <th style="width: 40%;">Full Name</th>
                        <th style="width: 25%;">Grade</th>
                        <th style="width: 25%;">Section</th>
                        <th style="width: 10%;">Select</th>
                    </tr>
                </thead>
                <tbody id="personnelTable">
                    <?php if ($result_all_students->num_rows > 0) : ?>
                        <?php while ($student = $result_all_students->fetch_assoc()) : ?>
                            <tr>
                                <td><?php echo htmlspecialchars(ucwords($student['first_name']) . " " . ucwords($student['last_name'])); ?></td>
                                <td><?php echo htmlspecialchars(ucwords($student['grade_level'])); ?></td>
                                <td><?php echo htmlspecialchars(ucwords($student['section_name'])); ?></td>
                                <td>
                                    <?php if ($student['id'] != $selected_student['id']) : ?>
                                        <!-- Only show the "Select" button for students who are not the selected student -->
                                        <form action="admin-s-3.php" method="GET">
                                            <input type="hidden" name="offender_id" value="<?php echo htmlspecialchars($student_id); ?>">
                                            <input type="hidden" name="victim_id" value="<?php echo htmlspecialchars($student['id']); ?>">
                                            <button type="submit" class="btn btn-success btn-block">Select</button>
                                        </form>
                                    <?php else : ?>
                                        <!-- Disable the button if the student is the same as the selected student -->
                                        <button type="button" class="btn btn-secondary btn-block" disabled>Selected</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="4" class="text-center">No students found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('searchInput').addEventListener('keyup', function() {
            var input = document.getElementById('searchInput').value.toLowerCase();
            var rows = document.querySelectorAll('#personnelTable tr');

            rows.forEach(function(row) {
                var name = row.cells[0].innerText.toLowerCase();
                var position = row.cells[1].innerText.toLowerCase();

                if (name.includes(input) || position.includes(input)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });
</script>
<?php
include "toast.php";
include "footer.php";
?>