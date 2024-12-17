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

// Assume user is authenticated and $admin_id is the logged-in admin's ID
$admin_id = $_SESSION['admin_id']; // This should be set during the login process

// Fetch the schedule data
$query = "SELECT date, description, location FROM schedules WHERE admin_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();

$schedule_events = [];
while ($row = $result->fetch_assoc()) {
    $schedule_events[] = [
        'title' => $row['description'] . ' at ' . $row['location'],
        'start' => $row['date']
    ];
}

// Handle schedule addition
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['description'], $_POST['selected_date'], $_POST['time'])) {
    $selected_date = $_POST['selected_date'];
    $description = $_POST['description'];
    $time = $_POST['time']; // Get time from form

    // Combine date and time for insertion
    $datetime = $selected_date . ' ' . $time;

    // Insert the new schedule into the database
    $insert_query = "INSERT INTO schedules (admin_id, date, description, location) VALUES (?, ?, ?, ?)"; // location is now used for storing extra info if needed
    $insert_stmt = $conn->prepare($insert_query);
    $insert_stmt->bind_param("isss", $admin_id, $datetime, $description, $time); // Use datetime for date and time

    if ($insert_stmt->execute()) {
        $message = "Schedule added successfully";
        // Refresh schedule events after adding a new event
        $schedule_events[] = [
            'title' => $description . ' at ' . $time,
            'start' => $datetime // Update to use the combined datetime
        ];
    } else {
        $message = "Error adding schedule: " . $insert_stmt->error;
    }
}
?>
<div id="main">

    <?php include "header.php"; ?>
    <div class="container-fluid">
        <div class="container-fluid bg-white mt-2 rounded-lg border mb-2 pb-2">
            <div class="row pt-3">
                <div class="col-md-12 justify-content-center">
                    <div id='calendar'></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Adding Schedule -->
    <div class="modal fade" id="addScheduleModal" tabindex="-1" aria-labelledby="addScheduleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="addScheduleModalLabel">Add Schedule</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="scheduleForm" action="" method="post">
                        <input type="hidden" id="selectedDate" name="selected_date">
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <input type="text" class="form-control" id="description" name="description" required>
                        </div>
                        <div class="mb-3">
                            <label for="time" class="form-label">Time</label>
                            <input type="time" class="form-control" id="time" name="time" required>
                        </div>
                        <button type="submit" class="btn btn-outline-success float-end">Add Schedule</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            events: <?php echo json_encode($schedule_events); ?>,
            eventColor: '#007bff',
            dateClick: function(info) {
                // Set the selected date in the hidden input field
                document.getElementById('selectedDate').value = info.dateStr;

                // Show the add schedule modal
                $('#addScheduleModal').modal('show');
            }
        });

        calendar.render();
    });
</script>

<style>
    .fc-daygrid-day:hover {
        cursor: pointer;
        background-color: #e0e0e0;
    }

    #calendar {
        max-height: 500px;
        color: green;
    }

    .fc-daygrid-day-number {
        color: #28a745 !important;
        text-decoration: none;
        /* Bootstrap success green color */
    }

    .fc-event-title {
        color: #28a745 !important;
        /* Bootstrap success green color */
    }

    .fc-daygrid-day {
        font-size: 1.2rem;
        color: #28a745 !important;

        /* Smaller font size */
    }

    .modal-content {
        font-size: 0.9rem;
        /* Smaller font size for the modal */
    }

    .fc-col-header-cell-cushion {
        color: #28a745 !important;
        text-decoration: none;
    }

    .fc-daygrid-day:hover {
        background-color: #d4edda;
        /* Lighter green background on hover */
        cursor: pointer;
    }
</style>
<?php
include "toast.php";
include "footer.php";
?>