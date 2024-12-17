<?php
ob_start();
include "head.php";
include "sidebar.php";
include "conn.php";

// Fetch all school years
$query = "SELECT id, year_start, year_end, graduated FROM school_year ORDER BY year_start ASC";
$result = $conn->query($query);

// Handle marking school year as graduated
if (isset($_POST['mark_graduated'])) {
    $school_year_id = $_POST['school_year_id'];

    // Mark the school year as graduated
    $query1 = "UPDATE school_year SET graduated = 1 WHERE id = ?";
    $stmt1 = $conn->prepare($query1);
    $stmt1->bind_param('i', $school_year_id);

    // Mark all students in the school year as graduated
    $query2 = "
    UPDATE students 
    SET graduated = 1 
    WHERE id IN (
        SELECT sa.student_id 
        FROM section_assignment sa 
        WHERE sa.school_year_id = ?)
    ";
    $stmt2 = $conn->prepare($query2);
    $stmt2->bind_param('i', $school_year_id);

    if ($stmt1->execute() && $stmt2->execute()) {
        $toast_message = "School Year updated successfully!";
        $toast_class = "success"; // Success class for the toast
        // Redirect to reload the page after successful update
        header("Location: ".$_SERVER['PHP_SELF']);
        exit(); // Stop further execution to avoid resubmitting the form
    } else {
        $toast_message = "Something went wrong while updating the School Year.";
        $toast_class = "danger"; // Error class for the toast
    }
    $stmt1->close(); // Close the statement
    $stmt2->close(); // Close the statement
}
?>

<div id="main">
    <?php include "header.php"; ?>

    <div class="container-fluid mb-5">
        <div class="container-fluid bg-white mt-2 rounded-lg pb-2 border">
            <!-- Header and Search Section -->
            <div class="row pt-3">
                <div class="col-md-6">
                    <div class="container-fluid p-2">
                        <h3>School Year Management</h3>
                    </div>
                </div>
            </div>

            <!-- School Year Table -->
            <table class="table table-bordered text-center">
                <thead style="background-color: #0C2D0B; color:white;">
                    <tr>
                        <th style="width:40%">School Year</th>
                        <th style="width:30%">Status</th>
                        <th style="width:30%">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['year_start'] . ' - ' . $row['year_end']); ?></td>
                            <td><?= $row['graduated'] ? 'Graduated' : 'Ongoing'; ?></td>
                            <td>
                                <?php if (!$row['graduated']): ?>
                                    <button class="btn btn-success mark-graduated-btn" data-id="<?= $row['id']; ?>"
                                            data-sy="<?= htmlspecialchars($row['year_start'] . ' - ' . $row['year_end']); ?>"
                                            data-bs-toggle="modal" data-bs-target="#confirmModal">
                                        Mark as Graduated
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-secondary" disabled>Graduated</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalLabel">Confirm Graduation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to mark <strong><span id="syToGraduate"></span></strong> as graduated? This action cannot be undone.
                    <input type="hidden" name="school_year_id" id="schoolYearId">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="mark_graduated" class="btn btn-success">Yes, Mark as Graduated</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Populate modal data dynamically
    document.querySelectorAll('.mark-graduated-btn').forEach(function (button) {
        button.addEventListener('click', function () {
            document.getElementById('syToGraduate').innerText = button.getAttribute('data-sy');
            document.getElementById('schoolYearId').value = button.getAttribute('data-id');
        });
    });
</script>

<?php
include "toast.php"; // Include toast.php here, which will display the toast
include "footer.php";
ob_end_flush(); // End output buffering and flush the output
?>
