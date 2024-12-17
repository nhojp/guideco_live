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

// Fetch all students with their related section and grade
$sql_students = "SELECT s.id, s.first_name, s.last_name, sec.section_name, sec.grade_level
                 FROM students s
                 INNER JOIN sections sec ON s.section_id = sec.id";
$result_students = $conn->query($sql_students);
?>
<div id="main">

    <?php include "header.php"; ?>
    <div class="container-fluid">
        <div class="container-fluid bg-white mt-2 rounded-lg border">
            <div class="row pt-3">
                <div class="col-md-6">
                    <div class="container-fluid p-2">
                        <h3><strong>Complain a Student</strong></h3>
                    </div>
                </div>
                <div class="col-md-6">
                    <input class="form-control" type="text" id="searchInput" placeholder="Search student">
                </div>
            </div>

            <table class="table table-hover table-bordered mt-2 text-center">
                <thead>
                    <tr>
                        <th style="width: 40%;">Name</th>
                        <th style="width: 25%;">Grade</th>
                        <th style="width: 25%;">Section</th>
                        <th style="width: 10%;">Select</th>
                    </tr>
                </thead>
                <tbody id="personnelTable">
                    <?php if ($result_students->num_rows > 0) : ?>
                        <?php while ($student = $result_students->fetch_assoc()) : ?>
                            <tr>
                                <td><?php echo ucwords($student['first_name']) . " " . ucwords($student['last_name']); ?></td>
                                <td><?php echo ucwords($student['grade_level']); ?></td>
                                <td><?php echo ucwords($student['section_name']); ?></td>
                                <td>
                                    <form action="admin-s-2.php" method="get">
                                        <input type="hidden" name="student_id" value="<?php echo $student['id']; ?>">
                                        <button type="submit" class="btn btn-outline-success btn-block">
                                            Next </button>
                                    </form>

                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="4">No students found</td>
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