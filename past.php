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

// Check if school_year is set in the URL
if (isset($_GET['school_year'])) {
    $school_year_id = $_GET['school_year'];

    // Query to fetch the school year info (e.g., "2023 - 2024")
    $school_year_query = "SELECT CONCAT(year_start, ' - ', year_end) AS school_year FROM school_year WHERE id = ?";
    $stmt = $conn->prepare($school_year_query);
    $stmt->bind_param('i', $school_year_id);  // 'i' stands for integer
    $stmt->execute();
    $school_year_result = $stmt->get_result();
    $school_year = $school_year_result->fetch_assoc()['school_year'];

    // Query to fetch students for the selected school year
    $query = "
        SELECT s.id, u.username, s.first_name, s.last_name, s.section_id, s.lrn,
                st.name AS strand_name, sec.section_name, sec.grade_level AS grade,
                CONCAT(sec.grade_level, ' - ', st.name, ' - ', sec.section_name) AS section_display,
                CONCAT(t.first_name, ' ', t.last_name) AS teacher_name
        FROM students s
        JOIN users u ON s.user_id = u.id
        JOIN section_assignment sa ON s.id = sa.student_id
        JOIN sections sec ON sa.section_id = sec.id
        JOIN strands st ON sec.strand_id = st.id
        JOIN teachers t ON sa.teacher_id = t.id
        WHERE sa.school_year_id = ?
        ORDER BY s.id ASC
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $school_year_id);  // 'i' stands for integer
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch the students from the result
    $students = $result->fetch_all(MYSQLI_ASSOC);
} else {
    // If no school year is selected, show a message or redirect
    echo "No school year selected.";
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
                        <h3>SY <strong><?php echo htmlspecialchars($school_year); ?></strong></h3>
                    </div>
                </div>
                <div class="col-md-6">
                    <form id="searchForm" method="GET" class="d-flex">
                        <input type="text" name="search_query" id="searchQuery" class="form-control" placeholder="Search Students" />
                    </form>
                </div>
            </div>

            <table class="table table-bordered text-center">
                <thead style="background-color: #0C2D0B; color:white;">
                    <tr>
                        <th style="width:20%">LRN</th>
                        <th style="width:30%">Full Name</th>
                        <th style="width:20%">Section</th>
                        <th style="width:20%">Teacher</th>
                        <th style="width:10%">Action</th> <!-- Column for the view button -->
                    </tr>
                </thead>
                <tbody id="studentsTableBody">
                    <?php foreach ($students as $student): ?>
                        <tr class="student-row">
                            <td><?php echo ucwords(htmlspecialchars($student['username'])); ?></td>
                            <td><?php echo ucwords(htmlspecialchars($student['first_name'] . ' ' . $student['last_name'])); ?></td>
                            <td><?php echo ucwords(htmlspecialchars($student['section_display'])); ?></td>
                            <td><?php echo ucwords(htmlspecialchars($student['teacher_name'])); ?></td>
                            <td>
                                <button class="btn btn-success" onclick="viewStudent(<?php echo $student['id']; ?>)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
    // Function to view student details
    function viewStudent(studentId) {
        // Redirect to the student detail view page
        window.location.href = 'admin-student-profile.php?id=' + studentId; // Adjust the page URL as necessary
    }

    // Function to filter table rows based on search query
    document.getElementById("searchQuery").addEventListener("input", function() {
        let query = this.value.toLowerCase();
        let rows = document.querySelectorAll(".student-row");

        rows.forEach(row => {
            let username = row.cells[0].textContent.toLowerCase();
            let fullName = row.cells[1].textContent.toLowerCase();
            let section = row.cells[2].textContent.toLowerCase();
            let teacher = row.cells[3].textContent.toLowerCase();

            if (username.includes(query) || fullName.includes(query) || section.includes(query) || teacher.includes(query)) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    });
</script>
<?php
include "toast.php";
include "footer.php";
?>