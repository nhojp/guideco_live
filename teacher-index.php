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

$query = "
    SELECT students.id, students.first_name, students.middle_name, students.last_name, 
           students.age, students.sex, sections.id as section_id, sections.section_name, sections.grade_level
    FROM students
    JOIN sections ON students.section_id = sections.id
    WHERE students.graduated != 1
";


$result = mysqli_query($conn, $query);
if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

// SQL query to fetch violations data
$violations_query = "SELECT id, violation_description FROM violation_list";
$violations_result = mysqli_query($conn, $violations_query);
if (!$violations_result) {
    die("Query failed: " . mysqli_error($conn));
}

// Handle form submission for reporting multiple students
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $selected_students = explode(',', $_POST['selected_students'] ?? '');
    $violation_description = $_POST['violation'];
    $teacher_id = $_SESSION['teacher_id'];

    // Fetch the violation ID from the violation description
    $violation_query = "SELECT id FROM violation_list WHERE violation_description = ?";
    $violation_stmt = $conn->prepare($violation_query);
    if ($violation_stmt) {
        $violation_stmt->bind_param("s", $violation_description);
        $violation_stmt->execute();
        $violation_stmt->bind_result($violation_id);
        $violation_stmt->fetch();
        $violation_stmt->close();

        if ($violation_id) {
            $stmt = $conn->prepare("INSERT INTO violations (student_id, violation_id, teacher_id) VALUES (?, ?, ?)");
            if ($stmt) {
                foreach ($selected_students as $student_id) {
                    $stmt->bind_param("iii", $student_id, $violation_id, $teacher_id);
                    $stmt->execute();
                }
                $stmt->close();
                $reportSuccess = true;
            } else {
                $errorMessage = "Failed to prepare violation report statement.";
            }
        } else {
            $errorMessage = "Invalid violation description.";
        }
    } else {
        $errorMessage = "Failed to prepare violation lookup statement.";
    }
}

// Reset result set pointers
mysqli_data_seek($violations_result, 0);
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

    .table-scroll {
        max-height: 400px;
        overflow-y: auto;
        border: 1px solid #ddd;
        border-radius: 5px;
}

    table tbody td {
        text-transform: capitalize;
}

    table th,
    .table td {
        padding: 15px;
        text-align: left;
        border-bottom: 1px solid #ddd;
}

    .table thead {
        position: sticky;
        top: 0;
        background-color: #0C2D0B;
        z-index: 1;
        color: white;
}

    .action-btn {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    color: #fff;
    text-decoration: none;
}

.custom-info {
    background-color: #238b45;
    border: 1px solid #238b45;
}

.custom-info:hover {
    background-color: #005a32;
    border: 1px solid #005a32;
}

.btn-info.active, 
.btn-info:focus, 
.btn-info:active {
    background-color: #28a745 !important; 
    border-color: #28a745 !important;     
    color: white !important;              
}


.btn-info.active i, 
.btn-info:focus i, 
.btn-info:active i {
    color: white !important; 
}


.btn-info:focus {
    outline: none !important;
    box-shadow: none !important;
}
</style>
<main class="flex-fill mt-5">
    <div class="container mt-4">
        <div class="row">
            <div class="container-fluid mt-2 mb-5">
                <div class="container-fluid mt-4 bg-light p-4 rounded">
                    <h4>Selected Students</h4>
                    <ul id="selectedStudentsList" class="list-group mb-3"></ul>
                    <form action="" method="POST">
                        <input type="hidden" name="selected_students" id="selected_students">
                        <div class="form-group">
                            <label for="violation">Violation</label>
                            <select class="form-control" id="violation" name="violation" required>
                                <option value="" disabled selected>Select a violation</option>
                                <?php while ($violation_row = mysqli_fetch_assoc($violations_result)) : ?>
                                    <option value="<?php echo htmlspecialchars($violation_row['violation_description']); ?>">
                                        <?php echo ucwords(htmlspecialchars($violation_row['violation_description'])); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-danger">Report Selected</button>
                    </form>
                </div>

                <div class="container-fluid bg-white mt-2 rounded-lg pb-2 border">
                    <div class="row pt-3">
                        <div class="col-md-6">
                            <h3><strong>Student List</strong></h3>
                        </div>
                        <div class="col-md-6">
                            <input class="form-control" type="text" id="searchInput" placeholder="Search a name or position...">
                        </div>
                    </div>
                    <?php if ($reportSuccess || !empty($errorMessage)) : ?>
                        <div class="alert <?php echo $reportSuccess ? 'alert-success' : 'alert-danger'; ?> mt-4">
                            <?php echo $reportSuccess ? 'Report sent successfully for selected students.' : htmlspecialchars($errorMessage); ?>
                        </div>
                    <?php endif; ?>

                    <div class="table-container mt-3">
                        <div class="table-scroll">
                            <table class="table table-hover border">
                                <thead class="thead-custom">
                                    <tr>
                                        <th>Full Name</th>
                                        <th>Grade</th>
                                        <th>Section</th>
                                        <th class="text-center">Select</th>
                                    </tr>
                                </thead>
                                <tbody id="personnelTable">
                                    <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                                        <tr>
                                            <td><?php echo ucfirst($row['first_name']) . ' ' . ucfirst($row['middle_name']) . ' ' . ucfirst($row['last_name']); ?></td>
                                            <td><?php echo ucfirst($row['grade_level']); ?></td>
                                            <td><?php echo ucfirst($row['section_name']); ?></td>
                                            <td class="text-center">
                                                <button class="btn btn-info action-btn custom-info select-student-btn" 
                                                        data-id="<?php echo $row['id']; ?>"
                                                        data-fullname="<?php echo ucfirst($row['first_name']) . ' ' . ucfirst($row['middle_name']) . ' ' . ucfirst($row['last_name']); ?>"
                                                        data-section="<?php echo ucfirst($row['section_name']); ?>">
                                                        <i class="fa-solid fa-check"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<script>
    document.addEventListener('DOMContentLoaded', function () {
    const selectedStudents = new Map();
    const selectedStudentsList = document.getElementById('selectedStudentsList');
    const selectedStudentsInput = document.getElementById('selected_students');

    function updateSelectedStudentsInput() {
        selectedStudentsInput.value = Array.from(selectedStudents.keys()).join(',');
    }

    function createRemoveButton(studentId) {
        const removeButton = document.createElement('button');
        removeButton.className = 'btn btn-sm btn-danger action-btn float-right';
        const icon = document.createElement('i');
        icon.className = 'fas fa-trash-alt';
        removeButton.appendChild(icon);
        removeButton.onclick = function () {
            selectedStudents.delete(studentId);
            document.querySelector(`li[data-id="${studentId}"]`).remove();
            updateSelectedStudentsInput();
           
            const button = document.querySelector(`.select-student-btn[data-id="${studentId}"]`);
            button.disabled = false;
            button.style.backgroundColor = '';  
            button.style.borderColor = '';  
        };

        return removeButton;
    }

 
    document.querySelectorAll('.select-student-btn').forEach(button => {
        button.addEventListener('click', function () {
            const studentId = this.getAttribute('data-id');
            const fullName = this.getAttribute('data-fullname');
            const section = this.getAttribute('data-section');

            if (!selectedStudents.has(studentId)) {
                selectedStudents.set(studentId, fullName);
                const listItem = document.createElement('li');
                listItem.className = 'list-group-item';
                listItem.textContent = `${fullName} (${section})`;
                listItem.setAttribute('data-id', studentId);
                listItem.appendChild(createRemoveButton(studentId));
                selectedStudentsList.appendChild(listItem);
                updateSelectedStudentsInput();
                
                
                this.style.backgroundColor = '#0C2D0B'; 
                this.style.borderColor = '#0C2D0B';
                this.disabled = true;  
            }
        });
    });

   
    document.getElementById('searchInput').addEventListener('keyup', function () {
        const query = this.value.toLowerCase();
        document.querySelectorAll('#personnelTable tr').forEach(row => {
            const text = row.innerText.toLowerCase();
            row.style.display = text.includes(query) ? '' : 'none';
        });
    });
});

</script>
<?php include 'footer.php'; ?>
