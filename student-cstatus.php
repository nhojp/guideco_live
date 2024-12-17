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
include "student-nav.php";
include "conn.php";

// Get the logged-in student ID
$student_id = $_SESSION['user_id']; // The student ID from the session

// Fetch student details from the students table
$query = "SELECT id, first_name, last_name, section_id FROM students WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

// Check if student data was fetched successfully
if ($student) {
    $student_id = $student['id'];
    $first_name = $student['first_name'];
    $last_name = $student['last_name'];
    $section_id = $student['section_id'];
} else {
    echo "No student found with user_id: $student_id";
    exit;
}

// Fetch counseling requests for the logged-in student
$query = "SELECT id, status, details, date, time, rating, rating_msg, scheduled FROM counseling WHERE student_id = ? ORDER BY date DESC, time DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

// Process the rating submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['rating']) && isset($_POST['counseling_id'])) {
    $rating = $_POST['rating'];
    $counseling_id = $_POST['counseling_id'];
    $rating_msg = $_POST['rating_msg'] ?? '';  // Get the rating message if exists

    // Validate the rating (ensure it's between 1 and 5)
    if ($rating >= 1 && $rating <= 5) {
        // Update the rating and message in the database
        $update_query = "UPDATE counseling SET rating = ?, rating_msg = ?, status = 'completed' WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("isi", $rating, $rating_msg, $counseling_id);
        $stmt->execute();

        // Set a success message
        $_SESSION['toast_message'] = "Thank you for your rating!";
        $_SESSION['toast_class'] = "success";
    } else {
        // Invalid rating
        $_SESSION['toast_message'] = "Invalid rating. Please select a valid rating.";
        $_SESSION['toast_class'] = "danger";
    }

    // Redirect to the same page to refresh the state
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
?>

<div id="main">
    <div class="container-fluid mt-5 pt-3">
        <div class="container-fluid bg-white mt-5 rounded-lg border">
            <div class="row pt-3 p-2">
                <h4 class="fw-bold">My Counseling Requests</h4>
                <p>Meet the counselor at the office at the scheduled time.</p>
                <!-- Counseling requests table -->
                <table class="table table-bordered text-center">
                    <thead>
                        <tr>
                            <th scope="col">Date & Time</th>
                            <th scope="col">Details</th>
                            <th scope="col">Status</th>
                            <th scope="col">Rating</th> <!-- Updated Rating column -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0) { ?>
                            <?php while ($row = $result->fetch_assoc()) { ?>
                                <tr>
                                    <td>
                                        <?php
                                        // Concatenate date and time
                                        $date_time = $row['date'] . ' ' . $row['time'];

                                        // Check if the date and time are invalid (set to "0000-00-00 00:00:00")
                                        if ($date_time == '0000-00-00 00:00:00' || empty($date_time)) {
                                            echo "Pending"; // Display "Pending" if invalid
                                        } else {
                                            echo date("Y-m-d H:i:s", strtotime($date_time)); // Display formatted date and time
                                        }
                                        ?>
                                    </td>
                                    <td><?php echo $row['details'] ?: "No details provided"; ?></td>
                                    <td>
                                        <?php
                                        // Display status based on the scheduled value
                                        if ($row['scheduled'] == 'no') {
                                            echo '<span class="badge bg-warning text-dark w-100 p-2">Pending</span>';
                                        } elseif ($row['scheduled'] == 'yes' && $row['status'] == 'pending') {
                                            echo '<span class="badge bg-info text-white w-100 p-2">Ongoing</span>';
                                        } else {
                                            echo '<span class="badge bg-success text-white w-100 p-2">Completed</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php if ($row['scheduled'] == 'yes' && $row['status'] != 'completed') { ?>
                                            <!-- Finish Button: Show if scheduled and not completed -->
                                            <button type="button" class="btn btn-outline-success w-100" data-bs-toggle="modal" data-bs-target="#ratingModal-<?php echo $row['id']; ?>">Finish</button>

                                            <!-- Rating Modal -->
                                            <div class="modal fade" id="ratingModal-<?php echo $row['id']; ?>" tabindex="-1" aria-labelledby="ratingModalLabel-<?php echo $row['id']; ?>" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header bg-success text-white">
                                                            <h5 class="modal-title" id="ratingModalLabel-<?php echo $row['id']; ?>">Rate Your Counseling Session</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                                                                <!-- Star Rating -->
                                                                <div class="d-flex justify-content-center mb-3">
                                                                    <?php for ($i = 1; $i <= 5; $i++) { ?>
                                                                        <input type="radio" name="rating" value="<?php echo $i; ?>" <?php echo ($row['rating'] == $i) ? 'checked' : ''; ?> class="btn-check" id="rating-<?php echo $row['id']; ?>-<?php echo $i; ?>">
                                                                        <label class="btn btn-outline-warning" for="rating-<?php echo $row['id']; ?>-<?php echo $i; ?>"><i class="fas fa-star"></i></label>
                                                                    <?php } ?>
                                                                </div>
                                                                <!-- Text Area for Feedback -->
                                                                <div class="mb-3">
                                                                    <textarea name="rating_msg" class="form-control" placeholder="What can we improve?" rows="3"><?php echo htmlspecialchars($row['rating_msg']); ?></textarea>
                                                                </div>
                                                                <!-- Hidden Counseling ID -->
                                                                <input type="hidden" name="counseling_id" value="<?php echo $row['id']; ?>">
                                                                <button type="submit" class="btn btn-success w-100">Complete</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php } else if ($row['status'] == 'completed') { ?>
                                            <!-- Display Stars after completion -->
                                            <?php for ($i = 1; $i <= 5; $i++) {
                                                echo $i <= $row['rating'] ? '<i class="fas fa-star text-warning"></i>' : '<i class="fas fa-star"></i>';
                                            } ?>
                                        <?php } else { ?>
                                            Pending
                                        <?php } ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="4">No counseling requests found.</td>
                            </tr>
                        <?php } ?>
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