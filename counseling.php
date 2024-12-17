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

if (!isset($_SESSION['loggedin']) || !isset($_SESSION['admin'])) {
    header('Location: index.php'); // Redirect if not logged in or not admin
    exit;
}

include "head.php";
include "sidebar.php";
include "conn.php";

// Handle the form submission (if any)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_datetime'])) {
    $requestId = $_POST['request_id'];
    $date = $_POST['date']; // Date input from the form
    $time = $_POST['time']; // Time input from the form

    // Update the date and time separately in the database
    $updateQuery = "UPDATE counseling SET date = '$date', time = '$time', scheduled = 'yes' WHERE id = '$requestId'";

    if (mysqli_query($conn, $updateQuery)) {
        $_SESSION['toast_message'] = 'Counseling request scheduled successfully!';
        $_SESSION['toast_class'] = 'success';
    } else {
        $_SESSION['toast_message'] = 'Error scheduling request!';
        $_SESSION['toast_class'] = 'danger';
    }

    // Redirect to refresh the page and display the toast message
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}


// Query to fetch pending counseling requests along with student details
$query = "SELECT c.id, c.student_id, c.details, s.first_name, s.last_name, c.scheduled, c.date, c.time
          FROM counseling c
          JOIN students s ON c.student_id = s.id
          WHERE c.status = 'pending'";
$result = mysqli_query($conn, $query);

// Query to fetch completed counseling requests with ratings
$completed_query = "SELECT c.id, c.student_id, c.details, c.rating, s.first_name, s.last_name, c.rating_msg
                    FROM counseling c
                    JOIN students s ON c.student_id = s.id
                    WHERE c.status = 'completed'";
$completed_result = mysqli_query($conn, $completed_query);

// Function to display stars based on rating
function displayStars($rating)
{
    $stars = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $rating) {
            $stars .= '<i class="fas fa-star text-warning"></i>';
        } else {
            $stars .= '<i class="fas fa-star"></i>';
        }
    }
    return $stars;
}
?>

<style>
    .star-rating {
        position: relative;
        cursor: pointer;
    }

    .star-rating:hover::after {
        content: attr(title);
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-80%);
        background-color: #000;
        color: #fff;
        padding: 15px;
        border-radius: 5px;
        white-space: nowrap;
        font-size: 12px;
    }
</style>

<div id="main">
    <?php include "header.php"; ?>
    <div class="container-fluid">
        <div class="container-fluid bg-white mt-2 rounded-lg border mb-2">
            <div class="row pt-3 pb-3">
                <div class="col-md-6">
                    <div class="container-fluid p-2">
                        <h3><strong>Counseling Requests</strong></h3>
                    </div>
                </div>
                <div class="col-md-6">
                    <input class="form-control" type="text" id="searchInput" placeholder="Search">
                </div>
            </div>
        </div>

        <!-- Tabs for Pending and Completed Requests -->
        <ul class="nav nav-tabs" id="requestTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link active" id="pending-tab" data-bs-toggle="tab" href="#pending" role="tab" aria-controls="pending" aria-selected="true">Pending</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="completed-tab" data-bs-toggle="tab" href="#completed" role="tab" aria-controls="completed" aria-selected="false">Completed</a>
            </li>
        </ul>

        <div class="tab-content" id="requestTabsContent">
            <!-- Pending Requests Tab -->
            <div class="tab-pane fade show active" id="pending" role="tabpanel" aria-labelledby="pending-tab">
                <table class="table mt-3 text-center table-bordered">
                    <thead>
                        <tr>
                            <th style="width: 25%;">Student Name</th>
                            <th>Details</th>
                            <th style="width: 15%;">Status</th>
                            <th style="width: 10%;">Preview</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                    <?php
                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                $studentName = $row['first_name'] . ' ' . $row['last_name'];
                                $details = $row['details'];

                                $scheduleStatus = $row['scheduled'] == 'yes' ? 'Scheduled' : 'No Schedule';
                        ?>
                                <tr>
                                    <td><?php echo ucwords($studentName); ?></td>
                                    <td><?php echo $details; ?></td>
                                    <td><?php echo $scheduleStatus; ?></td> <!-- New Column for Schedule Status -->
                                    <td>
                                        <!-- Preview Button for Modal -->
                                        <button class="btn btn-outline-success w-100" data-bs-toggle="modal" data-bs-target="#previewModal<?php echo $row['id']; ?>"><i class="fas fa-eye"></i></button>
                                    </td>
                                </tr>

                                <!-- Preview Modal for Each Request -->
                                <div class="modal fade" id="previewModal<?php echo $row['id']; ?>" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header bg-success text-white">
                                                <h5 class="modal-title" id="previewModalLabel">Request Details</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p><strong>From:</strong> <?php echo $studentName; ?></p>
                                                <p><?php echo $details; ?></p>
                                                <hr>
                                                <form method="POST" action="">
                                                    <div class="mb-3">
                                                        <label for="date<?php echo $row['id']; ?>" class="form-label">Select Date</label>
                                                        <?php
                                                        // Fetch the current date value if available
                                                        $scheduledDate = $row['scheduled'] == 'yes' ? $row['date'] : '';
                                                        ?>
                                                        <input type="date" class="form-control" id="date<?php echo $row['id']; ?>" name="date" value="<?php echo $scheduledDate; ?>" min="<?php echo date('Y-m-d'); ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="time<?php echo $row['id']; ?>" class="form-label">Select Time</label>
                                                        <?php
                                                        // Fetch the current time value if available
                                                        $scheduledTime = $row['scheduled'] == 'yes' ? $row['time'] : '';
                                                        ?>
                                                        <input type="time" class="form-control" id="time<?php echo $row['id']; ?>" name="time" value="<?php echo $scheduledTime; ?>" required>
                                                    </div>
                                                    <input type="hidden" name="request_id" value="<?php echo $row['id']; ?>">
                                                    <div class="modal-footer">
                                                        <button type="submit" name="submit_datetime" class="btn btn-outline-success">Confirm</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                        <?php }
                        } else {
                            echo "<tr><td colspan='3' class='text-center'>No pending requests</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Completed Requests Tab -->
            <div class="tab-pane fade" id="completed" role="tabpanel" aria-labelledby="completed-tab">
                <table class="table mt-3 text-center table-bordered">
                    <thead>
                        <tr>
                            <th style="width:25%;">Student Name</th>
                            <th>Details</th>
                            <th style="width:15%;">Rating</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <?php
                        if (mysqli_num_rows($completed_result) > 0) {
                            while ($row = mysqli_fetch_assoc($completed_result)) {
                                $studentName = $row['first_name'] . ' ' . $row['last_name'];
                                $details = $row['details'];
                                $rating = $row['rating'];
                                $rating_msg = $row['rating_msg']; // Fetch the rating message
                        ?>
                                <tr>
                                    <td><?php echo $studentName; ?></td>
                                    <td><?php echo $details; ?></td>
                                    <td>
                                        <!-- Display Rating as Stars with Hover Effect for Rating Message -->
                                        <span class="star-rating" data-rating="<?php echo $rating; ?>" title="<?php echo $rating_msg; ?>">
                                            <?php echo displayStars($rating); ?>
                                        </span>
                                    </td>
                                </tr>
                        <?php }
                        } else {
                            echo "<tr><td colspan='3' class='text-center'>No completed requests</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>

            </div>
        </div>
    </div>
</div>
<script>
    // Add event listener to the search input field
    document.getElementById('searchInput').addEventListener('input', function() {
        const filter = this.value.toLowerCase();  // Get the value of the input, convert to lowercase
        const rows = document.querySelectorAll('#tableBody tr'); // Get all rows from the table body

        // Loop through each row and check if any of the cells contain the search query
        rows.forEach(row => {
            const cells = row.querySelectorAll('td');  // Get all the cells in the current row
            let match = false;

            // Loop through each cell in the row to check if it contains the search term
            cells.forEach(cell => {
                if (cell.textContent.toLowerCase().includes(filter)) {
                    match = true;  // If a match is found, set match to true
                }
            });

            // Show or hide the row based on the match
            if (match) {
                row.style.display = '';  // Show the row if there's a match
            } else {
                row.style.display = 'none';  // Hide the row if there's no match
            }
        });
    });
</script>

<?php
include "toast.php";
include "footer.php";
?>