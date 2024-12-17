<?php
// Include the necessary files and initiate the database connection
include 'conn.php';

// Get the section ID from the query parameter
$section_id = isset($_GET['section_id']) ? intval($_GET['section_id']) : 0;

// Debugging: Print section_id to check if it's received correctly
// echo "<!-- Section ID: " . htmlspecialchars($section_id) . " -->";

// Fetch the section name
$section_name = '';
if ($section_id > 0) {
    $sql = "SELECT section_name FROM sections WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $section_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $section_name = $row['section_name'];
    }
    // Debugging: Print section_name to check if it's retrieved correctly
    // echo "<!-- Section Name: " . htmlspecialchars($section_name) . " -->";
}

// Fetch the students based on the section
$sql = "SELECT u.username, u.password, s.first_name, s.last_name
        FROM students s
        JOIN users u ON s.user_id = u.id";
if ($section_id > 0) {
    $sql .= " WHERE s.section_id = ?";
}
$stmt = $conn->prepare($sql);
if ($section_id > 0) {
    $stmt->bind_param("i", $section_id);
}
$stmt->execute();
$result = $stmt->get_result();
$students = [];
while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Student List</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</head>

<body>
    <div class="container mt-5">
        <h2>Student List<?php echo $section_name ? ' - ' . htmlspecialchars($section_name) : ''; ?></h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th style="width:50%;">Full Name</th>
                    <th style="width:25%;">Username</th>
                    <th style="width:25%;">Password</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($students) > 0) : ?>
                    <?php foreach ($students as $student) : ?>
                        <tr>
                            <td><?php echo ucwords(htmlspecialchars($student['first_name'] . ' ' . $student['last_name'])); ?></td>
                            <td><?php echo htmlspecialchars($student['username']); ?></td>
                            <td><?php echo htmlspecialchars($student['password']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="3">No students found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>

</html>
