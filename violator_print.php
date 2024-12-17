<?php
// Including the necessary files
include 'conn.php';
include 'head.php';

// SQL query to fetch violations data
$violations_query = "
    SELECT violations.id, students.id AS student_id, students.first_name, students.middle_name, students.last_name, 
    students.age, students.sex, sections.section_name, sections.grade_level, 
    violation_list.violation_description AS violation, 
    CASE
        WHEN violations.guard_id IS NOT NULL THEN guards.first_name
        WHEN violations.teacher_id IS NOT NULL THEN teachers.first_name
        WHEN violations.admin_id IS NOT NULL THEN admin.first_name
        ELSE 'Unknown'
    END AS reported_by_name,
    CASE
        WHEN violations.guard_id IS NOT NULL THEN 'Guard'
        WHEN violations.teacher_id IS NOT NULL THEN 'Teacher'
        WHEN violations.admin_id IS NOT NULL THEN 'Guidance Counselor'
        ELSE 'Unknown'
    END AS reported_by_type,
    violations.reported_at AS reportedAt
    FROM violations
    JOIN students ON violations.student_id = students.id
    JOIN sections ON students.section_id = sections.id
    LEFT JOIN guards ON violations.guard_id = guards.id
    LEFT JOIN teachers ON violations.teacher_id = teachers.id
    LEFT JOIN admin ON violations.admin_id = admin.id
    JOIN violation_list ON violations.violation_id = violation_list.id
    ORDER BY violations.reported_at DESC";

$violations_result = mysqli_query($conn, $violations_query);

if (!$violations_result) {
    die("Violations query failed: " . mysqli_error($conn));
}
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Intake-Sheet-Form-Annex-A</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style-form.css">
    <script>
        window.onload = function() {
            window.print();
        };
    </script>

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            /* Adjust as needed */
        }
    </style>
</head>
<div class="container-fluid">
    <div class="container-fluid bg-white mt-2 rounded-lg">
        <h3><strong>Violators List</strong></h3>
        <table class="table table-bordered text-center">
            <thead>
                <tr>
                    <th style="width:25%;">Name</th>
                    <th style="width:15%;">Grade</th>
                    <th style="width:20%;">Violation</th>
                    <th style="width:15%;">Reported by</th>
                    <th style="width:25%;">Reported at</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($violations_result)) : ?>
                    <tr>
                        <td><?php echo ucwords(htmlspecialchars($row['first_name'] . ' ' . $row['last_name'])); ?></td>
                        <td><?php echo ucwords(htmlspecialchars($row['grade_level'] . ' - ' . $row['section_name'])); ?></td>
                        <td><?php echo ucwords(htmlspecialchars($row['violation'])); ?></td>
                        <td><?php echo ucwords(htmlspecialchars($row['reported_by_name'])); ?></td>
                        <td><?php echo htmlspecialchars($row['reportedAt']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>