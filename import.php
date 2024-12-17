<?php
session_start();
ob_start();

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

require 'vendor/autoload.php'; // Include the PhpSpreadsheet autoload file

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['excel_file']) && $_FILES['excel_file']['error'] === UPLOAD_ERR_OK) {
        $filePath = $_FILES['excel_file']['tmp_name'];

        // Load the spreadsheet
        $spreadsheet = IOFactory::load($filePath);
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

        $conn->begin_transaction();
        try {
            // Get selected values from the form
            $section_id = $_POST['section_id'];
            $school_year_id = $_POST['school_year_id'];

            // Validate section existence and fetch associated teacher_id
            $stmt = $conn->prepare("SELECT teacher_id FROM sections WHERE id = ?");
            $stmt->bind_param("i", $section_id);
            $stmt->execute();
            $stmt->bind_result($teacher_id);
            $stmt->fetch();
            $stmt->close();

            if (!$teacher_id) {
                throw new Exception("Error: Section with ID $section_id does not exist or has no assigned teacher.");
            }

            // Loop through each row of the spreadsheet
            foreach ($sheetData as $index => $row) {
                if ($index < 10) continue; // Skip rows 1-9

                $full_name = trim($row['B']); // Column B (Full Name)
                $lrn = trim($row['C']); // Column C (LRN)
                $sex = trim($row['D']); // Column D (Sex)
                $barangay = trim($row['E']); // Column E (Barangay)

                if (empty($full_name) || empty($lrn) || empty($sex)) {
                    continue; // Skip rows with missing critical data
                }

                // Split full name by comma (last name, first and middle names)
                $name_parts = explode(',', $full_name);
                $last_name = trim($name_parts[0]); // Last name before the comma
                $first_middle_name = trim($name_parts[1]); // First and middle names after the comma

                // Split the first and middle names
                $first_middle_parts = explode(' ', $first_middle_name);
                $first_name = array_shift($first_middle_parts); // First word is the first name
                $middle_name = implode(' ', $first_middle_parts); // Remaining parts as middle name (could be empty)

                // Insert into the users table
                $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
                $stmt->bind_param("ss", $lrn, $lrn); // LRN as username and password
                $stmt->execute();
                $user_id = $stmt->insert_id;
                $stmt->close();

                // Insert into the students table
                $stmt = $conn->prepare("INSERT INTO students (user_id, first_name, middle_name, last_name, sex, barangay, section_id, lrn) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("isssssii", $user_id, $first_name, $middle_name, $last_name, $sex, $barangay, $section_id, $lrn);
                $stmt->execute();
                $student_id = $stmt->insert_id;
                $stmt->close();

                // Insert into the section_assignment table
                $stmt = $conn->prepare("INSERT INTO section_assignment (student_id, teacher_id, section_id, school_year_id) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iiii", $student_id, $teacher_id, $section_id, $school_year_id);
                $stmt->execute();
                $stmt->close();

                // Insert into mothers table
                $stmt = $conn->prepare("INSERT INTO mothers (student_id) VALUES (?)");
                $stmt->bind_param("i", $student_id);
                $stmt->execute();
                $stmt->close();

                // Insert into fathers table
                $stmt = $conn->prepare("INSERT INTO fathers (student_id) VALUES (?)");
                $stmt->bind_param("i", $student_id);
                $stmt->execute();
                $stmt->close();
            }

            // Commit the transaction
            $conn->commit();
            $_SESSION['toast_message'] = "Imported successfully!";
            $_SESSION['toast_class'] = "success";

            header("Location: import.php");
            exit;
        } catch (Exception $exception) {
            $conn->rollback();
            $_SESSION['toast_message'] = "Importing failed!";
            $_SESSION['toast_class'] = "danger";
        }
    }
}

// Fetch existing students with their related data
$result = $conn->query("SELECT s.id, u.username, s.first_name, s.last_name, 
           st.name AS strand_name, sec.section_name, sec.grade_level, 
           sy.year_start, sy.year_end, 
           CONCAT(st.name, ' - ', sec.section_name, ' (Grade ', sec.grade_level, ')') AS section_display,
           CONCAT(sy.year_start, ' - ', sy.year_end) AS school_year_display,
           CONCAT(t.first_name, ' ', t.last_name) AS teacher_name,
           u.password
    FROM students s 
    JOIN users u ON s.user_id = u.id
    JOIN section_assignment sa ON s.id = sa.student_id
    JOIN sections sec ON sa.section_id = sec.id
    JOIN strands st ON sec.strand_id = st.id
    JOIN school_year sy ON sa.school_year_id = sy.id
    JOIN teachers t ON sa.teacher_id = t.id");
$students = $result->fetch_all(MYSQLI_ASSOC);

// Fetch sections for the dropdown
$sections_result = $conn->query("SELECT s.id, CONCAT(' Grade ', s.grade_level, ' ',st.name, ' - ', s.section_name) AS section_display 
    FROM sections s 
    JOIN strands st ON s.strand_id = st.id");
$sections = $sections_result->fetch_all(MYSQLI_ASSOC);

// Fetch school years for the dropdown
$school_years_result = $conn->query("SELECT id, CONCAT(year_start, ' - ', year_end) AS year_display 
                                     FROM school_year 
                                     WHERE graduated != 1");
$school_years = $school_years_result->fetch_all(MYSQLI_ASSOC);

?>

<div id="main">

    <?php include "header.php"; ?>

    <div class="container-fluid mb-5">
        <div class="container-fluid bg-white mt-2 rounded-lg pb-2 border">
            <div class="row pt-3">
                <div class="d-flex align-items-center">
                    <button class="btn btn-outline-danger me-3" onclick="window.location.href='student_page.php'">
                        <i class="fas fa-arrow-left"></i>
                    </button>
                    <h3 class="font-weight-bold mb-0">Import from Excel</h3>
                </div>
            </div>


            <form method="POST" enctype="multipart/form-data" class="mb-4">
                <div class="row pb-2 pt-4">
                    <div class="col-md-6">
                        <!-- Section Dropdown -->
                        <div class="mb-3">
                            <label for="section_id" class="form-label ">Select Section:</label>
                            <select name="section_id" class="form-select form-control" required>
                                <?php foreach ($sections as $section): ?>
                                    <option value="<?php echo $section['id']; ?>"><?php echo ucwords($section['section_display']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <!-- School Year Dropdown -->
                        <div class="mb-3">
                            <label for="school_year_id" class="form-label">Select School Year:</label>
                            <select name="school_year_id" class="form-select form-control" required>
                                <?php foreach ($school_years as $school_year): ?>
                                    <option value="<?php echo $school_year['id']; ?>"><?php echo $school_year['year_display']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-10">
                        <input type="file" name="excel_file" class="form-control" accept=".xls,.xlsx" required>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-success btn-block">Import</button>
                    </div>
                </div>
            </form>
        </div>
        <!-- Error Modal -->
        <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="errorModalLabel">Error</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>There was an error importing the students. Please try again.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
include "toast.php";
include "footer.php";
?>