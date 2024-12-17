<?php
include 'conn.php';
function calculateAge($birthdate)
{
    $today = new DateTime();
    $dob = new DateTime($birthdate);
    $age = $today->diff($dob)->y;
    return $age;
}

if (isset($_POST['student_id'])) {
    // Fetch student details based on selected student_id
    $student_id = $_POST['student_id'];

    $sql = "
        SELECT s.first_name AS student_first_name, 
               s.middle_name AS student_middle_name, 
               s.last_name AS student_last_name, 
               s.birthdate, s.sex,
               sec.section_name, 
               sec.grade_level, 
                CONCAT(t.first_name, ' ', t.last_name) AS teacher_full_name,
               m.name as mother_name, 
               m.contact_number as mother_contact, 
               m.occupation AS mother_occupation, 
               m.address AS mother_address,
               f.name as father_name, 
               f.contact_number as father_contact, 
               f.occupation AS father_occupation, 
               f.address AS father_address
        FROM students s
        INNER JOIN sections sec ON s.section_id = sec.id
        INNER JOIN teachers t ON sec.teacher_id = t.id
        LEFT JOIN mothers m ON s.id = m.student_id
        LEFT JOIN fathers f ON s.id = f.student_id
        WHERE s.id = $student_id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode($row);
    }
    exit();
}

// Fetch all students for the dropdown
$sql_students = "SELECT id, first_name, last_name FROM students";
$result_students = $conn->query($sql_students);

$students = [];
if ($result_students->num_rows > 0) {
    while ($row = $result_students->fetch_assoc()) {
        $students[] = $row;
    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    // Fetch the form data
    $student_first_name = $_POST['student_first_name'];
    $student_middle_name = $_POST['student_middle_name'];
    $student_last_name = $_POST['student_last_name'];
    $birthdate = $_POST['birthdate'];
    $sex = $_POST['sex'];
    $victim_grade = $_POST['victimGrade'];
    $victim_section = $_POST['victimSection'];
    $victim_adviser = $_POST['victimAdviser'];
    $mother_name = $_POST['mother_name'];
    $mother_occupation = $_POST['mother_occupation'];
    $mother_address = $_POST['mother_address'];
    $mother_contact = $_POST['mother_contact'];
    $father_name = $_POST['father_name'];
    $father_occupation = $_POST['father_occupation'];
    $father_address = $_POST['father_address'];
    $father_contact = $_POST['father_contact'];

    // Escape the variables to prevent SQL injection
    $student_first_name = mysqli_real_escape_string($conn, $student_first_name);
    $student_middle_name = mysqli_real_escape_string($conn, $student_middle_name);
    $student_last_name = mysqli_real_escape_string($conn, $student_last_name);
    $birthdate = mysqli_real_escape_string($conn, $birthdate);
    $victim_age = calculateAge($birthdate);
    $sex = mysqli_real_escape_string($conn, $sex);
    $victim_grade = mysqli_real_escape_string($conn, $victim_grade);
    $victim_section = mysqli_real_escape_string($conn, $victim_section);
    $victim_adviser = mysqli_real_escape_string($conn, $victim_adviser);
    $mother_name = mysqli_real_escape_string($conn, $mother_name);
    $mother_occupation = mysqli_real_escape_string($conn, $mother_occupation);
    $mother_address = mysqli_real_escape_string($conn, $mother_address);
    $mother_contact = mysqli_real_escape_string($conn, $mother_contact);
    $father_name = mysqli_real_escape_string($conn, $father_name);
    $father_occupation = mysqli_real_escape_string($conn, $father_occupation);
    $father_address = mysqli_real_escape_string($conn, $father_address);
    $father_contact = mysqli_real_escape_string($conn, $father_contact);

    // Prepare SQL INSERT query for complaints table
    $sql_insert = "
        INSERT INTO complaints (
            `victimFirstName`,`victimMiddleName`,`victimLastName`,`victimDOB`,`victimAge`,`victimSex`,
            `victimGrade`,`victimSection`, `victimAdviser`, 
            `motherName`,`motherOccupation`,`motherAddress`,`motherContact`, 
            `fatherName`,`fatherOccupation`, `fatherAddress`, `fatherContact`
        ) VALUES (
            '$student_first_name', '$student_middle_name', '$student_last_name', '$birthdate', '$victim_age', '$sex',
                    '$victim_grade', '$victim_section', '$victim_adviser',
            '$mother_name', '$mother_occupation', '$mother_address', '$mother_contact',
            '$father_name', '$father_occupation', '$father_address', '$father_contact'
        )";

    if ($conn->query($sql_insert) === TRUE) {
        echo "Record submitted successfully.";
    } else {
        echo "Error: " . $sql_insert . "<br>" . $conn->error;
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Info</title>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
</head>

<body>

    <!-- Searchable dropdown -->
    <div class="form-row mt-3">
        <div class="col-md-12">
            <strong>Search Student:</strong>
            <select id="student-select" class="form-control" name="student_id">
                <option value="">Select a student</option>
                <?php foreach ($students as $student): ?>
                    <option value="<?= $student['id'] ?>"><?= $student['first_name'] . ' ' . $student['last_name'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <!-- Student Details Form -->
    <form id="student-details-form" method="POST">
        <div class="form-row mt-3">
            <div class="col-md-6">
                <strong>First Name:</strong>
                <input type="text" class="form-control" name="student_first_name">
            </div>
            <div class="col-md-6">
                <strong>Middle Name:</strong>
                <input type="text" class="form-control" name="student_middle_name">
            </div>
            <div class="col-md-6">
                <strong>Last Name:</strong>
                <input type="text" class="form-control" name="student_last_name">
            </div>
        </div>
        <div class="form-row mt-3">
            <div class="col-md-6">
                <strong>Birthdate:</strong>
                <input type="text" class="form-control" name="birthdate">
            </div>
            <div class="col-md-6">
                <strong>Sex:</strong>
                <input type="text" class="form-control" name="sex">
            </div>
        </div>

        <div class="form-row mt-3">
            <div class="col-md-3"><strong>Grade:</strong>
                <input type="text" class="form-control" name="victimGrade">
            </div>
            <div class="col-md-3"><strong>Section:</strong>
                <input type="text" class="form-control" name="victimSection">
            </div>
            <div class="col-md-6"><strong>Adviser:</strong>
                <input type="text" class="form-control" name="victimAdviser">
            </div>
        </div>

        <div class="form-row mt-3">
            <div class="col-md-6">
                <strong>Mother's Name:</strong>
                <input type="text" class="form-control" name="mother_name">
            </div>
            <div class="col-md-6">
                <strong>Mother's Occupation:</strong>
                <input type="text" class="form-control" name="mother_occupation">
            </div>
            <div class="col-md-6">
                <strong>Mother's Address:</strong>
                <input type="text" class="form-control" name="mother_address">
            </div>
            <div class="col-md-6">
                <strong>Mother's Contact:</strong>
                <input type="text" class="form-control" name="mother_contact">
            </div>

            <div class="col-md-6">
                <strong>Father's Name:</strong>
                <input type="text" class="form-control" name="father_name">
            </div>
            <div class="col-md-6">
                <strong>Father's Occupation:</strong>
                <input type="text" class="form-control" name="father_occupation">
            </div>
            <div class="col-md-6">
                <strong>Father's Address:</strong>
                <input type="text" class="form-control" name="father_address">
            </div>
            <div class="col-md-6">
                <strong>Father's Contact:</strong>
                <input type="text" class="form-control" name="father_contact">
            </div>
        </div>

        
        <!-- Submit Button -->
        <button type="submit" class="btn btn-primary mt-3" name="submit">Submit</button>
    </form>

    <script>
        $(document).ready(function() {
            // Initialize Select2 dropdown
            $('#student-select').select2();

            // Trigger AJAX to fetch student details
            $('#student-select').change(function() {
                var studentId = $(this).val();
                if (studentId) {
                    $.ajax({
                        url: '', // Current page
                        method: 'POST',
                        data: {
                            student_id: studentId
                        },
                        success: function(response) {
                            var student = JSON.parse(response);
                            // Populate form fields with student data
                            $('[name="student_first_name"]').val(student.student_first_name);
                            $('[name="student_middle_name"]').val(student.student_middle_name);
                            $('[name="student_last_name"]').val(student.student_last_name);
                            $('[name="birthdate"]').val(student.birthdate);
                            $('[name="sex"]').val(student.sex);
                            $('[name="victimGrade"]').val(student.grade_level); // Correctly use grade_level from the DB
                            $('[name="victimSection"]').val(student.section_name); // Correctly use section_name from the DB
                            $('[name="victimAdviser"]').val(student.teacher_full_name); // Combine adviser's first and last name
                            $('[name="mother_name"]').val(student.mother_name);
                            $('[name="mother_occupation"]').val(student.mother_occupation);
                            $('[name="mother_address"]').val(student.mother_address);
                            $('[name="mother_contact"]').val(student.mother_contact);
                            $('[name="father_name"]').val(student.father_name);
                            $('[name="father_occupation"]').val(student.father_occupation);
                            $('[name="father_address"]').val(student.father_address);
                            $('[name="father_contact"]').val(student.father_contact);
                        }
                    });
                }
            });
        });
    </script>

</body>

</html>