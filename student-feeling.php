<?php
session_start();
ob_start();

include 'head.php';
include 'student-nav.php';
include 'conn.php';

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || !isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$student_id = $_SESSION['user_id'];

// Fetch student's feeling history from the database
$query = "SELECT physical_feeling, emotional_feeling, mental_feeling, created_at 
          FROM feelings 
          WHERE student_id = ? 
          ORDER BY created_at DESC";

if ($stmt = $conn->prepare($query)) {
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    echo "Error: " . $conn->error;
}

?>
<main class="flex-fill mt-5">

    <div class="container mt-5">
        <h1 class="text-center">Your Feeling History</h1>

        <!-- Display Form Error if exists -->
        <?php if (!empty($form_error)): ?>
            <div class="alert alert-danger">
                <?php echo $form_error; ?>
            </div>
        <?php endif; ?>

        <!-- Table displaying student's feeling history -->
        <table class="table table-striped mt-4">
            <thead>
                <tr>
                    <th style="width: 25%;">Physically</th>
                    <th style="width: 25%;">Emotionally</th>
                    <th style="width: 25%;">Mentally</th>
                    <th style="width: 25%;">Date & Time</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo ucwords(htmlspecialchars($row['physical_feeling'])); ?></td>
                            <td><?php echo ucwords(htmlspecialchars($row['emotional_feeling'])); ?></td>
                            <td><?php echo ucwords(htmlspecialchars($row['mental_feeling'])); ?></td>
                            <td><?php echo date("F j, Y, g:i a", strtotime($row['created_at'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center">No feeling history available.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>
<?php
$stmt->close();
?>

<!-- Optional: Include footer -->
<?php include 'footer.php'; ?>