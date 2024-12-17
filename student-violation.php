<?php
include "conn.php";
include "head.php";

// Fetch student_id from URL
$student_id = $_GET['student_id'] ?? null;
include "student-nav.php";

// Function to get the name of a guard, teacher, or admin by ID
function get_name_by_id($conn, $id, $role)
{
    // Define the table and column names based on the role
    if ($role == 'guard') {
        $table = 'guards'; // assuming there is a guards table
        $first_name_column = 'first_name';
        $last_name_column = 'last_name';
    } elseif ($role == 'teacher') {
        $table = 'teachers'; // assuming there is a teachers table
        $first_name_column = 'first_name';
        $last_name_column = 'last_name';
    } elseif ($role == 'admin') {
        $table = 'admin'; // assuming there is an admins table
        $first_name_column = 'first_name';
        $last_name_column = 'last_name';
    } else {
        return "Unknown"; // if no valid role is provided
    }

    // Prepare and execute the query for the given role
    $sql_name = "SELECT CONCAT($first_name_column, ' ', $last_name_column) AS name FROM $table WHERE id = ?";
    if ($stmt_name = $conn->prepare($sql_name)) {
        $stmt_name->bind_param("i", $id);
        $stmt_name->execute();
        $result_name = $stmt_name->get_result();
        if ($result_name->num_rows > 0) {
            $row_name = $result_name->fetch_assoc();
            return $row_name['name'];
        }
    }
    return "Unknown"; // return Unknown if no result is found
}

// Check if student_id is set and fetch violations
if ($student_id) {
    // Query to fetch violations for the given student_id
    $sql_violations = "SELECT v.id, v.reported_at, v.violation_id, v.guard_id, v.teacher_id, v.admin_id, vi.violation_description 
                       FROM violations v 
                       JOIN violation_list vi ON v.violation_id = vi.id 
                       WHERE v.student_id = ?";
    if ($stmt = $conn->prepare($sql_violations)) {
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result_violations = $stmt->get_result();
        $violation_count = $result_violations->num_rows;  // Get the count of violations
    }
}
?>

<!-- HTML code starts here -->
<main class="flex-fill mt-5">
    <div class="container mt-5 bg-white">
        <?php
        if ($student_id) {
            // Display the warning message with respect to violations
            echo '<div class="alert alert-warning" role="alert">
                    You have <strong class="text-danger">' . $violation_count . '</strong> violations right now. If you exceed 5 violations, there will be corresponding penalties or consequences, and they may no longer be eligible to receive a good moral certificate.
                  </div>';

            if ($violation_count > 0) {
                // Display violations in a table
                echo '<table class="table table-bordered mt-4 bg-white">
                        <thead>
                            <tr>
                                <th>Violation</th>
                                <th>Reported At</th>
                                <th>Reported By</th>
                            </tr>
                        </thead>
                        <tbody>';

                while ($row_violations = $result_violations->fetch_assoc()) {
                    // Fetch guard, teacher, or admin name
                    $reporter_name = "Unknown";
                    if (!empty($row_violations['guard_id'])) {
                        $reporter_name = get_name_by_id($conn, $row_violations['guard_id'], 'guard');
                    } elseif (!empty($row_violations['teacher_id'])) {
                        $reporter_name = get_name_by_id($conn, $row_violations['teacher_id'], 'teacher');
                    } elseif (!empty($row_violations['admin_id'])) {
                        $reporter_name = get_name_by_id($conn, $row_violations['admin_id'], 'admin');
                    }

                    echo '<tr>
                            <td>' . htmlspecialchars(ucwords($row_violations['violation_description'])) . '</td>
                            <td>' . htmlspecialchars($row_violations['reported_at']) . '</td>
                            <td>' . htmlspecialchars(ucwords($reporter_name)) . '</td>
                          </tr>';
                }

                echo '</tbody></table>';
            } else {
                echo "No violations found for this student.";
            }
        }
        ?>
    </div>
</main>

<?php include 'footer.php'; ?>
