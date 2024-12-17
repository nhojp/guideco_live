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

// Check if there's a toast message in the session
if (isset($_SESSION['toast_message']) && isset($_SESSION['toast_class'])) {
    $toast_message = $_SESSION['toast_message'];
    $toast_class = $_SESSION['toast_class'];

    // Unset the session variables so the message doesn't appear again
    unset($_SESSION['toast_message']);
    unset($_SESSION['toast_class']);
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
    $admin_id = $_SESSION['admin_id'];

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
            $stmt = $conn->prepare("INSERT INTO violations (student_id, violation_id, admin_id) VALUES (?, ?, ?)");
            if ($stmt) {
                foreach ($selected_students as $student_id) {
                    $stmt->bind_param("iii", $student_id, $violation_id, $admin_id);
                    $stmt->execute();
                }
                $stmt->close();
                
                // Success: Set Toast message and class
                $_SESSION['toast_message'] = "Reported successfully!";
                $_SESSION['toast_class'] = "success";  // Bootstrap success class

                // Redirect to violators.php after success
                header('Location: violators.php');
                exit;
            } else {
                $_SESSION['toast_message'] = "Failed to prepare violation report statement.";
                $_SESSION['toast_class'] = "danger";
            }
        } else {
            $_SESSION['toast_message'] = "Invalid violation description.";
            $_SESSION['toast_class'] = "warning";
        }
    } else {
        $_SESSION['toast_message'] = "Failed to prepare violation lookup statement.";
        $_SESSION['toast_class'] = "danger";
    }
}


// Reset result set pointers
mysqli_data_seek($violations_result, 0);
?>
<div id="main">

    <?php include "header.php"; ?>

    <div class="container-fluid">
        <div class="container-fluid bg-white mt-2 rounded-lg border shadow-sm">
            <div class="row">
                <!-- Student List Section -->
                <div class="col-md-8">
                    <div class="container-fluid bg-white m-2">
                        <div class="row mb-3 pt-3">
                            <div class="col-md-6">
                                <h3><strong>Student List</strong></h3>
                            </div>
                            <div class="col-md-6">
                                <input class="form-control" type="text" id="searchInput" placeholder="Search by name or position..." />
                            </div>
                        </div>

                        <!-- Table Section with Scrollable Content -->
                        <div class="table-wrapper" style="position: relative; max-height: 400px; overflow-y: auto;">
                            <table class="table table-hover border text-center">
                                <thead class="thead-custom bg-primary text-white">
                                    <tr>
                                        <th>Full Name</th>
                                        <th>Grade</th>
                                        <th>Section</th>
                                        <th>Select</th>
                                    </tr>
                                </thead>
                                <tbody id="personnelTable">
                                    <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                                        <tr>
                                            <td><?php echo ucfirst($row['first_name']) . ' ' . ucfirst($row['middle_name']) . ' ' . ucfirst($row['last_name']); ?></td>
                                            <td><?php echo ucfirst($row['grade_level']); ?></td>
                                            <td><?php echo ucfirst($row['section_name']); ?></td>
                                            <td class="text-center">
                                                <button class="btn btn-outline-success select-student-btn w-100" data-id="<?php echo $row['id']; ?>"
                                                    data-fullname="<?php echo ucfirst($row['first_name']) . ' ' . ucfirst($row['middle_name']) . ' ' . ucfirst($row['last_name']); ?>"
                                                    data-section="<?php echo ucfirst($row['section_name']); ?>">
                                                    &plus;
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Violation Reporting Section -->
                <div class="col-md-4">
                    <div class="row">
                        <div class="container-fluid mt-2 bg-light p-2 rounded-lg shadow-sm" style="position: relative; max-height: 300px; overflow-y: auto;">
                            <h4 class="text-center mt-2 mb-2">Selected students</h4>
                            <ul id="selectedStudentsList" class="list-group mb-4">
                                <!-- Dynamically populated list of selected students -->
                            </ul>
                        </div>
                    </div>
                    <div class="row">
                        <div class="container-fluid mt-2 bg-light p-2 rounded-lg shadow-sm">
                            <form action="" method="POST">
                                <input type="hidden" name="selected_students" id="selected_students">
                                <div class="form-group mb-3">
                                    <label for="violation" class="font-weight-bold">Select Violation</label>
                                    <select class="form-control" id="violation" name="violation" required>
                                        <option value="" disabled selected>Select a violation</option>
                                        <?php while ($violation_row = mysqli_fetch_assoc($violations_result)) : ?>
                                            <option value="<?php echo htmlspecialchars($violation_row['violation_description']); ?>">
                                                <?php echo ucwords(htmlspecialchars($violation_row['violation_description'])); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-danger w-100 mt-3">Report Selected</button>
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
    document.addEventListener('DOMContentLoaded', function() {
        const selectedStudents = new Map();
        const selectedStudentsList = document.getElementById('selectedStudentsList');
        const selectedStudentsInput = document.getElementById('selected_students');

        function updateSelectedStudentsInput() {
            selectedStudentsInput.value = Array.from(selectedStudents.keys()).join(',');
        }

        function createRemoveButton(studentId) {
            const removeButton = document.createElement('button');
            removeButton.className = 'btn btn-sm btn-outline-danger float-right';
            removeButton.textContent = '×';
            removeButton.onclick = function() {
                selectedStudents.delete(studentId);
                document.querySelector(`li[data-id="${studentId}"]`).remove();
                updateSelectedStudentsInput();
            };
            return removeButton;
        }

        document.querySelectorAll('.select-student-btn').forEach(button => {
            button.addEventListener('click', function() {
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
                }
            });
        });

        document.getElementById('searchInput').addEventListener('keyup', function() {
            const query = this.value.toLowerCase();
            document.querySelectorAll('#personnelTable tr').forEach(row => {
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });
        });
    });
</script>

<?php
include "toast.php";
include "footer.php";
?>