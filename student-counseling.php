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

if (!isset($_SESSION['loggedin']) || !isset($_SESSION['user_id'])) {
    header('Location: index.php'); // Redirect if not logged in
    exit;
}

include "head.php";
include "sidebar.php";
include "conn.php";

// Fetch student details from the students table
$student_id = $_SESSION['user_id']; // Get the user_id from session
$query = "SELECT id, first_name, last_name, section_id FROM students WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

// Ensure we have valid student details
if ($student) {
    // Now use $student['id'] for the student_id insert in the counseling table
    $student_id = $student['id'];
    $section_id = $student['section_id'];
    $first_name = $student['first_name'];
    $last_name = $student['last_name'];
} else {
    echo "No student found with user_id: $student_id";
    exit;
}

// Process the form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $status = 'pending'; // Default status
    $details = $_POST['details'];

    // Concatenate any selected predefined options with the custom details if present
    $selected_reasons = isset($_POST['reason']) ? implode(", ", $_POST['reason']) : '';
    if (!empty($selected_reasons)) {
        $details = $selected_reasons . (empty($details) ? '' : ", " . $details);
    }

    // Insert counseling request into database (Date and time removed)
    $query = "INSERT INTO counseling (student_id, section_id, last_name, first_name, status, details) 
              VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iissss", $student_id, $section_id, $last_name, $first_name, $status, $details);

    if ($stmt->execute()) {
        // Success message
        $_SESSION['toast_message'] = "Counseling requested successfully! We'll be right back!";
        $_SESSION['toast_class'] = "success";
    } else {
        // Error message
        $_SESSION['toast_message'] = "Error requesting counseling: " . $stmt->error;
        $_SESSION['toast_class'] = "danger";
    }

    // Redirect back to the same page to show the message
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

include 'student-nav.php';
?>

<div id="main">
    <div class="container-fluid mt-5 pt-2">
        <div class="container-fluid bg-white rounded-lg border mt-5 mb-2">
            <div class="row pt-3">
                <!-- Heading and Button -->
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="fw-bold">Request Counseling</h4>
                    <a href="student-cstatus.php"><button class="btn btn-success btn-sm">Status</button></a>
                </div>

                <!-- Add the note here -->
                <div class="container-fluid mt-3">
                    <div class="alert alert-info">
                        <strong>Note:</strong> After sending a request, you must wait until the guidance counselor approves it.
                    </div>
                </div>
                <form method="POST">
                    <!-- Predefined Reasons (Checkboxes) -->
                    <div class="mb-3">
                        <label for="reason" class="form-label">Why are you requesting counseling?</label>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="reason[]" value="I'm having difficulty understanding certain subjects" id="reason_subjects">
                            <label class="form-check-label" for="reason_subjects">I'm having difficulty understanding certain subjects</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="reason[]" value="I'm struggling to keep up with the course load" id="reason_course_load">
                            <label class="form-check-label" for="reason_course_load">I'm struggling to keep up with the course load</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="reason[]" value="I need help improving my grades in specific subjects" id="reason_grades">
                            <label class="form-check-label" for="reason_grades">I need help improving my grades in specific subjects</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="reason[]" value="I've been feeling anxious and overwhelmed lately" id="reason_anxiety">
                            <label class="form-check-label" for="reason_anxiety">I've been feeling anxious and overwhelmed lately</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="reason[]" value="I feel constantly tired and unmotivated" id="reason_tired">
                            <label class="form-check-label" for="reason_tired">I feel constantly tired and unmotivated</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="reason[]" value="I'm being bullied or experiencing harassment" id="reason_bullying">
                            <label class="form-check-label" for="reason_bullying">I'm being bullied or experiencing harassment</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="reason[]" value="I’m facing pressure from my family about my academic performance" id="reason_family_pressure">
                            <label class="form-check-label" for="reason_family_pressure">I’m facing pressure from my family about my academic performance</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="reason[]" value="I lack motivation and have trouble focusing on my studies" id="reason_motivation">
                            <label class="form-check-label" for="reason_motivation">I lack motivation and have trouble focusing on my studies</label>
                        </div>
                    </div>

                    <!-- Custom Details -->
                    <div class="mb-3">
                        <label for="details" class="form-label">Additional Details</label>
                        <textarea name="details" class="form-control" rows="4"></textarea>
                    </div>

                    <button type="submit" class="btn btn-outline-success float-right mb-2">Request Counseling</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
include "toast.php";
include "footer.php";
?>