<?php
ob_start();
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

include "head.php";
include "sidebar.php";
include "conn.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['export'])) {

    
    $selected_section = $_POST['section_id'];

    // Fetch data for the header
    $section_query = $conn->prepare("SELECT s.section_name, s.grade_level, st.name AS strand, st.description, CONCAT(t.first_name, ' ', t.last_name) AS adviser 
                                     FROM sections s
                                     JOIN strands st ON s.strand_id = st.id
                                     JOIN teachers t ON s.teacher_id = t.id
                                     WHERE s.id = ?");
    $section_query->bind_param('i', $selected_section);
    $section_query->execute();
    $section_info = $section_query->get_result()->fetch_assoc();

    $students_query = $conn->prepare("
    SELECT 
        CONCAT(s.last_name, ', ', s.first_name, 
               IF(COALESCE(s.middle_name, '') = '', '', CONCAT(' ', UPPER(SUBSTRING(s.middle_name, 1, 1)), '.'))
        ) AS full_name, 
        s.lrn, s.sex, s.barangay
    FROM students s
    JOIN section_assignment sa ON s.id = sa.student_id
    WHERE sa.section_id = ?
    ORDER BY s.sex, s.last_name, s.first_name
");
    $students_query->bind_param('i', $selected_section);
    $students_query->execute();
    $students = $students_query->get_result()->fetch_all(MYSQLI_ASSOC);

    // Count students by sex
    $male_count = count(array_filter($students, fn($s) => $s['sex'] === 'Male'));
    $female_count = count(array_filter($students, fn($s) => $s['sex'] === 'Female'));
    $total_count = $male_count + $female_count;

    // Create Spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Set header
    // Make the header in A1 bold and centered
    $sheet->setCellValue('A1', "OFFICIAL ENROLLMENT SY (" . date('Y') . "-" . (date('Y') + 1) . ")");
    $sheet->mergeCells('A1:F1');
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('A1')->getFont()->setBold(true);
    $sheet->getStyle('A1')->getFont()->setBold(true); // Make the text bold
    $sheet->getStyle('A1')->getFont()->getColor()->setARGB('FF000000'); // Apply black color (using hex code)
    $sheet->mergeCells('A1:F1');
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // Make the text in B3 and B4 bold
    $sheet->setCellValue('B3', 'ACADEMIC');
    $sheet->setCellValue('B4', ucwords($section_info['description']));
    $sheet->getStyle('B3:B4')->getFont()->setBold(true);
    // Make the section name bold, red
    $sheet->setCellValue('B5', ucwords($section_info['section_name']));
    $sheet->getStyle('B5')->getFont()->setBold(true);
    $sheet->getStyle('B5')->getFont()->setBold(true); // Make the text bold
    $sheet->getStyle('B5')->getFont()->getColor()->setARGB('FFFF0000'); // Apply red color (using hex code)

    // Make the adviser name bold and maroon
    $sheet->setCellValue('B6', ucwords($section_info['adviser']));
    $sheet->getStyle('B6')->getFont()->setBold(true);
    $sheet->getStyle('B6')->getFont()->getColor()->setARGB('800000'); // Maroon color

    // For the cells with 'MALE', 'FEMALE', 'TOTAL', and their respective counts:
    $sheet->setCellValue('E3', 'MALE');
    $sheet->setCellValue('F3', $male_count);
    $sheet->setCellValue('E4', 'FEMALE');
    $sheet->setCellValue('F4', $female_count);
    $sheet->setCellValue('E5', 'TOTAL');
    $sheet->setCellValue('F5', $total_count);
    $sheet->getStyle('F5')->getFont()->setBold(true); // Make the total count value bold

    // Apply borders and bold to these cells
    $sheet->getStyle('E3:F5')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    $sheet->getStyle('E3:F5')->getFont()->setBold(true);

    // Headers for students
    $sheet->setCellValue('A8', 'NO.');
    $sheet->setCellValue('B8', 'FULL NAME');
    $sheet->setCellValue('C8', 'LRN');
    $sheet->setCellValue('D8', 'GENDER');
    $sheet->setCellValue('E8', 'BARANGAY');
    $sheet->setCellValue('F8', 'STRAND');

    // Add male and female sections
    $row = 9;
    foreach (['Male', 'Female'] as $gender) {
        // Merge and label gender section
        $sheet->mergeCells("A$row:F$row");
        $sheet->getStyle("A$row:F$row")->getFont()->setBold(true);
        $sheet->setCellValue("A$row", strtoupper($gender));
        $sheet->getStyle("A$row")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("A$row")->getFont()->setBold(true);
        $row++;

        // Add student rows
        $no = 1;
        foreach ($students as $student) {
            if ($student['sex'] === $gender) {
                $sheet->setCellValue("A$row", $no++);
                $sheet->setCellValue("B$row", ucwords($student['full_name']));
                $sheet->setCellValueExplicit("C$row", $student['lrn'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $sheet->getStyle("C$row")->getNumberFormat()->setFormatCode('#');
                $sheet->setCellValue("D$row", ucwords($student['sex']));
                $sheet->setCellValue("E$row", ucwords($student['barangay']));
                $sheet->setCellValue("F$row", ucwords($section_info['strand']));
                $row++;
            }
        }
    }

    // Apply borders to the data
    $last_row = $row - 1;
    $sheet->getStyle("A8:F$last_row")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

    // Save the file to the output buffer
    $writer = new Xlsx($spreadsheet);

    // Clear any previous output buffer
    ob_clean();
    // Output the file as an Excel file
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    // Assuming $section_info contains the section name and year
    $section_name = $section_info['section_name']; // Section name from your query
    $year = date('Y'); // Current year

    // Format the filename with section name and academic year
    $filename = "enrollment_{$section_name}_{$year}.xlsx";

    // Set the Content-Disposition header
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    // Send the file to the browser
    $writer->save('php://output');

    exit;
}
?>
<div id="main">
<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" role="dialog" aria-labelledby="loadingModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="loadingModalLabel">Please Wait</h5>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
                <p class="text-center mt-2">Exporting data, please wait...</p>
            </div>
        </div>
    </div>
</div>

    <?php include "header.php"; ?>

    <div class="container-fluid mb-5">
        <div class="container-fluid bg-white mt-2 rounded-lg pb-2 border">
            <div class="row pt-3">
                <div class="d-flex align-items-center">
                    <button class="btn btn-outline-danger me-3" onclick="window.location.href='student_page.php'">
                        <i class="fas fa-arrow-left"></i>
                    </button>
                    <h3 class="font-weight-bold mb-0">Export to Excel</h3>
                </div>
            </div>
            <div class="row pt-3">
                <!-- Form Section -->
                <form method="POST" action="">
                    <div class="d-flex justify-content-start gap-2">
                        <select name="section_id" id="section_id" class="form-select">
                            <?php
                            $sections_result = $conn->query("SELECT id, CONCAT(grade_level, ' - ', section_name) AS section_display FROM sections ORDER BY section_name ASC");
                            $sections = $sections_result->fetch_all(MYSQLI_ASSOC);
                            foreach ($sections as $section) {
                                // Apply ucwords to make the section display in title case
                                $section_display = ucwords($section['section_display']);

                                // Check if the current section is selected
                                $selected = (isset($_POST['section_id']) && $_POST['section_id'] == $section['id']) ? 'selected' : '';

                                // Output the option element with the ucwords applied
                                echo "<option value='{$section['id']}' $selected>$section_display</option>";
                            }
                            ?>

                        </select>

                        <!-- Export Button (Solid Success) -->
                        <button type="submit" name="export" class="btn btn-success">
                            <i class="fas fa-download"></i> Export
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <div class="row">
            <div class="container">
                <?php
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['section_id'])) {
                    $selected_section = $_POST['section_id'];

                    // Fetch data for the header
                    $section_query = $conn->prepare("SELECT s.section_name, s.grade_level, st.name AS strand, st.description, CONCAT(t.first_name, ' ', t.last_name) AS adviser 
                                 FROM sections s
                                 JOIN strands st ON s.strand_id = st.id
                                 JOIN teachers t ON s.teacher_id = t.id
                                 WHERE s.id = ?");
                    $section_query->bind_param('i', $selected_section);
                    $section_query->execute();
                    $section_info = $section_query->get_result()->fetch_assoc();

                    // Fetch students in the selected section grouped by sex
                    $students_query = $conn->prepare("
    SELECT 
        CONCAT(s.last_name, ', ', s.first_name, 
               IF(COALESCE(s.middle_name, '') = '', '', CONCAT(' ', UPPER(SUBSTRING(s.middle_name, 1, 1)), '.'))
        ) AS full_name, 
        s.lrn, s.sex, s.barangay
    FROM students s
    JOIN section_assignment sa ON s.id = sa.student_id
    WHERE sa.section_id = ?
    ORDER BY s.sex, s.last_name, s.first_name
");
                    $students_query->bind_param('i', $selected_section);
                    $students_query->execute();
                    $students = $students_query->get_result()->fetch_all(MYSQLI_ASSOC);

                ?>
                    <table class="table table-hover table-bordered mt-2 text-center">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Full Name</th>
                                <th>LRN</th>
                                <th>Gender</th>
                                <th>Barangay</th>
                                <th>Strand</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            foreach (['Male', 'Female'] as $gender) {
                                echo "<tr><td colspan='6' style='font-weight: bold; text-align: center;' colspan='6'>" . strtoupper($gender) . "</td></tr>";
                                foreach ($students as $student) {
                                    if ($student['sex'] === $gender) {
                                        echo "<tr>";
                                        echo "<td>{$no}</td>";
                                        echo "<td>" . ucwords($student['full_name']) . "</td>";
                                        echo "<td>" . $student['lrn'] . "</td>";
                                        echo "<td>" . ucwords($student['sex']) . "</td>";
                                        echo "<td>" . ucwords($student['barangay']) . "</td>";
                                        echo "<td>" . ucwords($section_info['strand']) . "</td>";
                                        echo "</tr>";
                                        $no++;
                                    }
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                <?php
                }
                ?>

            </div>
        </div>
    </div>
</div>
<?php
include "footer.php";
?>
<script>
    // Show the loading modal when the export process is triggered
    function showLoadingModal() {
        $('#loadingModal').modal('show');
    }

    // Hide the loading modal after export is complete
    function hideLoadingModal() {
        $('#loadingModal').modal('hide');
    }
</script>


<script>
    // Automatically submit the form when the dropdown value changes
    document.getElementById('section_id').addEventListener('change', function() {
        this.form.submit();
    });
</script>