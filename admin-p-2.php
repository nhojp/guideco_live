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

// Ensure the selected person information is available
if (!isset($_POST['person_id']) || !isset($_POST['person_type'])) {
    // Redirect or handle the case where no person is selected
    header('Location: admin-p-list.php');
    exit;
}

$person_id = $_POST['person_id'];
$person_type = $_POST['person_type'];

// Fetch the name of the selected personnel (teacher or guard)
$sql_person = "";
if ($person_type === 'teacher') {
    $sql_person = "SELECT * FROM teachers WHERE id = $person_id";
} elseif ($person_type === 'guard') {
    $sql_person = "SELECT * FROM guards WHERE id = $person_id";
}

$result_person = $conn->query($sql_person);
if ($result_person->num_rows > 0) {
    $person = $result_person->fetch_assoc();
    $person_name = $person['first_name'] . " " . $person['last_name'];
} else {
    $person_name = "Unknown";
}

// Fetch all students with their related section and grade
$sql_students = "SELECT s.id, s.first_name, s.last_name, sec.section_name, sec.grade_level as grade
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
                            <h4>
                                <strong>Victim of <span class="text-danger"><?php echo ucwords($person_name); ?></span></strong>
                            </h4>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <input class="form-control" type="text" id="searchInput" placeholder="Search a name or section...">
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
                        <?php if ($result_students->num_rows > 0) : ?>
                            <?php while ($student = $result_students->fetch_assoc()) : ?>
                                <tr>
                                    <td><?php echo ucwords($student['first_name']) . " " . ucwords($student['last_name']); ?></td>
                                    <td><?php echo ucwords($student['grade']); ?></td>
                                    <td><?php echo ucwords($student['section_name']); ?></td>
                                    <td>
                                        <form action="admin-p-3.php" method="get">
                                            <input type="hidden" name="student_id" value="<?php echo $student['id']; ?>">
                                            <input type="hidden" name="person_id" value="<?php echo $person_id; ?>">
                                            <input type="hidden" name="person_type" value="<?php echo $person_type; ?>">
                                            <button type="submit" class="btn btn-success btn-block">Select</button>
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

</div>

<?php
include "toast.php";
include "footer.php";
?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('searchInput').addEventListener('keyup', function() {
            var input = document.getElementById('searchInput').value.toLowerCase();
            var rows = document.querySelectorAll('#personnelTable tr');

            rows.forEach(function(row) {
                var name = row.cells[0].innerText.toLowerCase();
                var grade = row.cells[1].innerText.toLowerCase();
                var section = row.cells[2].innerText.toLowerCase();

                if (name.includes(input) || grade.includes(input) || section.includes(input)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });
</script>