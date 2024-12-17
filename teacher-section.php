<?php
session_start();
include 'conn.php';
include 'head.php';
include 'teacher-nav.php';

// Check if user is logged in and is a teacher
if (!isset($_SESSION['loggedin']) || !isset($_SESSION['teacher'])) {
    header('Location: index.php'); // Redirect if not logged in or not a teacher
    exit;
}

function generateId($conn)
{
    $sql = "SELECT MAX(id) AS max_id FROM students";
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

// Get the teacher_id from the session
$teacher_id = $_SESSION['teacher_id'];

// SQL query to fetch section details for the current teacher
$section_query = "
    SELECT sections.id as section_id, sections.section_name, sections.grade_level
    FROM sections
    WHERE sections.teacher_id = ?
";
$section_stmt = $conn->prepare($section_query);
$section_stmt->bind_param("i", $teacher_id);
$section_stmt->execute();
$section_result = $section_stmt->get_result();

if (!$section_result) {
    die("Query failed: " . mysqli_error($conn));
}

$section_data = $section_result->fetch_assoc();

if (!$section_data) {
    die("No section found for this teacher.");
}

// SQL query to fetch students data for the current section
$query = "
    SELECT students.id, students.first_name, students.middle_name, students.last_name, 
           students.age, students.sex, sections.id as section_id, sections.section_name, sections.grade_level
    FROM students
    JOIN sections ON students.section_id = sections.id
    WHERE sections.id = ?
";
$student_stmt = $conn->prepare($query);
$student_stmt->bind_param("i", $section_data['section_id']);
$student_stmt->execute();
$result = $student_stmt->get_result();

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

// Fetch violations from violation_list table
$violation_list_query = "SELECT id, violation_description FROM violation_list";
$violation_list_result = $conn->query($violation_list_query);

if (!$violation_list_result) {
    die("Query failed: " . $conn->error);
}

$violation_list = [];
while ($row = $violation_list_result->fetch_assoc()) {
    $violation_list[] = $row;
}

// Handle form submission for adding a student
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['first_name'])) {
    $generated_username = $_POST['generated_username'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $section_id = $_POST['section_id']; // This should be the section ID of the current teacher's section

    $insert_query = "
        INSERT INTO students (id, first_name, last_name, section_id)
        VALUES (?, ?, ?, ?)
    ";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("sssi", $generated_username, $first_name, $last_name, $section_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "<div class='alert alert-success' role='alert'>Student added successfully.</div>";
    } else {
        echo "<div class='alert alert-danger' role='alert'>Failed to add student.</div>";
    }

    $stmt->close();
}
?>
<style>
    * {
        font-family: 'Poppins', sans-serif;
    }
    .btn-custom {
        background-color: #1F5F1E; 
        color: white; 
        border: none; 
    }

    .btn-custom:hover {
        background-color: #389434; 
        color: white; 
    }

    .btn-custom:focus, .btn-custom:active {
        box-shadow: none; 
        outline: none; 
    }

    .thead-custom {
        background-color: #0C2D0B;
        color: white;
    }

    .btn-circle {
        width: 35px;   
        height: 35px;  
        border-radius: 50%; 
        display: flex;
        justify-content: center; 
        align-items: center;      
        padding: 0;
    }

    .table-container {
        max-height: 400px; 
        overflow-y: auto; 
    }
</style>

<main class="flex-fill mt-5 mb-5">
    <div class="container mt-4">
        <div class="container-fluid bg-white mt-2 rounded-lg">
            <div class="row pt-3">
                <div class="col-md-6">
                    <div class="container-fluid p-2">
                        <h3><strong>
                                <?php echo ucfirst($section_data['grade_level']) . ' - ' . ucfirst($section_data['section_name']); ?>
                            </strong>
                        </h3>
                    </div>
                </div>
                <div class="col-md-6">
                    <input class="form-control" type="text" id="searchInput" placeholder="Search a name or position...">
                </div>
                
            </div>
            <div class="table-container">
                <div class="table-responsive">
                    <table class="table text-center table-hover" style="width: 100%;">
                        <thead class="thead-custom">
                            <tr>
                                <th style="width:40%;">Full Name</th>
                                <th style="width:25%;">Grade</th>
                                <th style="width:25%;">Section</th>
                                <th style="width:10%;">Report</th>
                            </tr>
                        </thead>
                        <tbody id="students_table">
                            <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                                <tr data-section-id="<?php echo $row['section_id']; ?>" data-grade-level="<?php echo $row['grade_level']; ?>">
                                    <td><?php echo ucfirst($row['first_name']) . ' ' . ucfirst($row['last_name']); ?>
                                    </td>
                                    <td><?php echo ucfirst($row['grade_level']); ?></td>
                                    <td><?php echo ucfirst($row['section_name']); ?></td>
                                    <td><button class="btn btn-danger btn-block" data-bs-toggle="modal" data-bs-target="#reportModal" data-id="<?php echo $row['id']; ?>" data-fullname="<?php echo ucfirst($row['first_name']) . ' ' . ucfirst($row['middle_name']) . ' ' . ucfirst($row['last_name']); ?>" data-section="<?php echo ucfirst($row['section_name']); ?>">Report</button></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Report Modal -->
<div class="modal fade" id="reportModal" tabindex="-1" role="dialog" aria-labelledby="reportModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-guideco text-white">
                <h5 class="modal-title" id="reportModalLabel">Report Student</h5>
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal" aria-label="Close">x</button>
            </div>
            <div class="modal-body">
                <form method="POST" action="report.php">
                    <div class="form-group">
                        <label for="reported_student_id">Student ID:</label>
                        <input type="text" id="reported_student_id" name="reported_student_id" class="form-control" readonly>
                    </div>
                    <div class="form-group">
                        <label for="violation">Violation:</label>
                        <select id="violation" name="violation" class="form-control" required>
                            <?php foreach ($violation_list as $violation) : ?>
                                <option value="<?php echo $violation['id']; ?>"><?php echo $violation['violation_description']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="details">Details:</label>
                        <textarea id="details" name="details" class="form-control" rows="3" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-danger">Report</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<script>
    // Set the full name and ID for the report modal
    $('#reportModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var studentId = button.data('id');
        var fullName = button.data('fullname');
        var section = button.data('section');

        var modal = $(this);
        modal.find('#reported_student_id').val(studentId);
        modal.find('#reported_student_fullname').text(fullName);
        modal.find('#reported_student_section').text(section);
    });
</script>
<script>
    $('#reportModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget)
        var id = button.data('id')
        var fullname = button.data('fullname')
        var section = button.data('section')

        var modal = $(this)
        modal.find('.modal-body #student_id').val(id)
        modal.find('.modal-body #full_name').val(fullname)
        modal.find('.modal-body #section').val(section)
    });

    document.getElementById('violation').addEventListener('change', function() {
        var othersDetail = document.getElementById('others-detail');
        if (this.value === 'Others') {
            othersDetail.style.display = 'block';
        } else {
            othersDetail.style.display = 'none';
        }
    });

    document.addEventListener("DOMContentLoaded", function() {
        const searchInput = document.getElementById('searchInput');
        const rows = document.querySelectorAll("tbody tr");

        searchInput.addEventListener("input", function() {
            const searchTerm = searchInput.value.toLowerCase().trim();

            rows.forEach(row => {
                const victimName = row.querySelector("td:nth-child(1)").textContent.toLowerCase();
                const complainedPerson = row.querySelector("td:nth-child(2)").textContent.toLowerCase();

                if (victimName.includes(searchTerm) || complainedPerson.includes(searchTerm)) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });
        });
    });

    document.getElementById('printButton').addEventListener('click', function() {
        const sectionId = document.getElementById('printSection').value;
        let url = 'admin-student-print.php'; // Default URL

        if (sectionId) {
            url += '?section_id=' + sectionId;
        }

        window.open(url, '_blank');
    });

    document.addEventListener("DOMContentLoaded", function() {
        const printButton = document.getElementById('printButton');

        printButton.addEventListener('click', function() {
            const sectionId = <?php echo json_encode($section_data['section_id']); ?>;
            let url = 'teacher-student-print.php'; // Default URL

            if (sectionId) {
                url += '?section_id=' + sectionId;
            }

            window.open(url, '_blank');
        });
    });
</script>