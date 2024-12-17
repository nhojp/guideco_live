<?php
include "head.php";
include "sidebar.php";
include "conn.php";
// Handle section creation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_section'])) {
        $section_name = ucwords(trim($_POST['section_name'])); // ucwords for section name
        $strand_id = $_POST['strand_id'];
        $grade_level = $_POST['grade_level']; // New field for grade level
        $teacher_id = $_POST['teacher_id']; // Get teacher_id from form

        // Prepare and bind
        $stmt = $conn->prepare("INSERT INTO sections (section_name, strand_id, grade_level, teacher_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssii", $section_name, $strand_id, $grade_level, $teacher_id); // 's' for string, 'i' for integer

        if ($stmt->execute()) {
            // Success: Show success toast
            $toast_message = "Section added successfully!";
            $toast_class = "success";
        } else {
            // Error: Show error toast
            $toast_message = "Something went wrong while adding the section.";
            $toast_class = "danger";
        }

        $stmt->close(); // Close the statement
    }

    // Handle section edit
    if (isset($_POST['edit_section'])) {
        $section_id = $_POST['section_id'];
        $section_name = ucwords(trim($_POST['edit_section_name']));
        $strand_id = $_POST['edit_strand_id'];
        $grade_level = $_POST['edit_grade_level'];
        $teacher_id = $_POST['edit_teacher_id'];

        $stmt = $conn->prepare("UPDATE sections SET section_name = ?, strand_id = ?, grade_level = ?, teacher_id = ? WHERE id = ?");
        $stmt->bind_param("ssiii", $section_name, $strand_id, $grade_level, $teacher_id, $section_id);

        if ($stmt->execute()) {
            // Success: Show success toast
            $toast_message = "Section edited successfully!";
            $toast_class = "success";
        } else {
            // Error: Show error toast
            $toast_message = "Something went wrong while editing the section.";
            $toast_class = "danger";
        }

        $stmt->close(); // Close the statement
    }

    // Handle section deletion
    if (isset($_POST['delete_section'])) {
        $section_id = $_POST['delete_section_id'];

        $stmt = $conn->prepare("DELETE FROM sections WHERE id = ?");
        $stmt->bind_param("i", $section_id);

        if ($stmt->execute()) {
            // Success: Show success toast
            $toast_message = "Section deleted successfully!";
            $toast_class = "success";
        } else {
            // Error: Show error toast
            $toast_message = "Something went wrong while deleting the section.";
            $toast_class = "danger";
        }

        $stmt1->close(); // Close the statement
        $stmt2->close(); // Close the statement

    }
}

// Fetch sections with assigned teachers
$sections = $conn->query("SELECT sec.id, sec.section_name, st.name AS strand_name, sec.grade_level, t.first_name, t.last_name, sec.teacher_id, sec.strand_id
                           FROM sections sec 
                           JOIN strands st ON sec.strand_id = st.id
                           LEFT JOIN teachers t ON sec.teacher_id = t.id");

// Fetch strands for dropdown
$strands = $conn->query("SELECT * FROM strands");

// Fetch teachers for dropdown
$teachers = $conn->query("SELECT * FROM teachers");

// Grade levels for dropdown (only 11 and 12)
$grade_levels = [
    '11' => 'Grade 11',
    '12' => 'Grade 12',
];
?>

<div id="main">

    <?php include "header.php"; ?>
    <div class="container-fluid">
        <div class="container-fluid bg-white mt-2 rounded-lg border">
            <div class="row pt-3">
                <div class="col-md-6">
                    <div class="container-fluid p-2">
                        <h3><strong>Section List</strong></h3>
                    </div>
                </div>
                <div class="col-md-6">
                    <input class="form-control" type="text" id="searchInput" placeholder="Search Section">
                </div>
            </div>

            <div class="row justify-content-end">
                <div class="col-md-1">
                    <div class="dropdown ">
                        <button type="button" class="btn btn-success w-100" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-plus"></i> Add
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#addSectionModal">Add section</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Add Section Modal -->
            <div class="modal fade" id="addSectionModal" tabindex="-1" role="dialog" aria-labelledby="addSectionModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header text-white bg-success">
                            <h5 class="modal-title" id="addSectionModalLabel">Add Section</h5>
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal" aria-label="Close">
                                <span>&times;</span>
                            </button>
                        </div>
                        <form method="POST">
                            <div class="modal-body">
                                <div class="form-group">
                                    <input type="text" name="section_name" class="form-control" placeholder="Section Name" required>
                                </div>

                                <div class="form-group">
                                    <label for="strand">Select Strand:</label>
                                    <select name="strand_id" class="form-control" required>
                                        <?php if ($strands->num_rows > 0): ?>
                                            <?php while ($strand = $strands->fetch_assoc()): ?>
                                                <option value="<?php echo htmlspecialchars($strand['id']); ?>">
                                                    <?php echo htmlspecialchars($strand['name']); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <option value="">No strands available</option>
                                        <?php endif; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="grade_level">Select Grade Level:</label>
                                    <select name="grade_level" class="form-control" required>
                                        <?php foreach ($grade_levels as $key => $value): ?>
                                            <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="teacher">Select Teacher:</label>
                                    <select name="teacher_id" class="form-control" required>
                                        <?php if ($teachers->num_rows > 0): ?>
                                            <?php while ($teacher = $teachers->fetch_assoc()): ?>
                                                <option value="<?php echo htmlspecialchars($teacher['id']); ?>">
                                                    <?php echo ucwords(htmlspecialchars($teacher['first_name'] . ' ' . $teacher['last_name'])); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <option value="">No teachers available</option>
                                        <?php endif; ?>
                                    </select>
                                </div>
                                <button type="submit" name="add_section" class="btn btn-outline-success" style="width: 100%;">Add Section</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>


        <!-- Sections Table -->
        <div class="table-container">
            <table class="table table-hover table-bordered mt-2 text-center">
                <thead>
                    <tr>
                        <th>Section Name</th>
                        <th>Strand</th>
                        <th>Grade Level</th>
                        <th>Assigned Teacher</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <?php if ($sections->num_rows > 0): ?>
                        <?php while ($section = $sections->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo ucwords(htmlspecialchars($section['section_name'])); ?></td>
                                <td><?php echo ucwords(htmlspecialchars($section['strand_name'])); ?></td>
                                <td><?php echo htmlspecialchars($section['grade_level']); ?></td>
                                <td><?php echo ucwords(htmlspecialchars($section['first_name'] . ' ' . $section['last_name'])); ?></td>
                                <td>
                                    <button class="btn btn-outline-success" data-toggle="modal" data-target="#editSectionModal<?php echo $section['id']; ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" data-toggle="modal" data-target="#deleteSectionModal<?php echo $section['id']; ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>

                            <!-- Edit Section Modal -->
                            <div class="modal fade" id="editSectionModal<?php echo $section['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="editSectionModalLabel" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header text-white bg-success">

                                            <h5 class="modal-title" id="editSectionModalLabel">Edit Section</h5>
                                            <button type="button" class="btn-danger btn btn-circle" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <form method="POST">
                                            <div class="modal-body">
                                                <input type="hidden" name="section_id" value="<?php echo $section['id']; ?>">

                                                <div class="form-group">
                                                    <input type="text" name="edit_section_name" value="<?php echo ucwords(htmlspecialchars($section['section_name'])); ?>" class="form-control" required>
                                                </div>

                                                <div class="form-group">
                                                    <label for="edit_strand">Select Strand:</label>
                                                    <select name="edit_strand_id" class="form-control" required>
                                                        <?php foreach ($strands as $strand): ?>
                                                            <option value="<?php echo $strand['id']; ?>" <?php echo ($strand['id'] == $section['strand_id']) ? 'selected' : ''; ?>>
                                                                <?php echo $strand['name']; ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>

                                                <div class="form-group">
                                                    <label for="edit_grade_level">Select Grade Level:</label>
                                                    <select name="edit_grade_level" class="form-control" required>
                                                        <?php foreach ($grade_levels as $key => $value): ?>
                                                            <option value="<?php echo $key; ?>" <?php echo ($key == $section['grade_level']) ? 'selected' : ''; ?>>
                                                                <?php echo $value; ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>

                                                <div class="form-group">
                                                    <label for="edit_teacher">Select Teacher:</label>
                                                    <select name="edit_teacher_id" class="form-control" required>
                                                        <?php foreach ($teachers as $teacher): ?>
                                                            <option value="<?php echo $teacher['id']; ?>" <?php echo ($teacher['id'] == $section['teacher_id']) ? 'selected' : ''; ?>>
                                                                <?php echo ucwords($teacher['first_name'] . ' ' . $teacher['last_name']); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>

                                                <button type="submit" name="edit_section" class="btn btn-outline-success" style="width: 100%;">Update Section</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <!-- Delete Section Modal -->
                            <div class="modal fade" id="deleteSectionModal<?php echo $section['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="deleteSectionModalLabel" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header text-white bg-success">

                                            <h5 class="modal-title" id="deleteSectionModalLabel">Delete Section</h5>
                                            <button type="button" class="btn-danger btn btn-circle" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <form method="POST">
                                            <div class="modal-body">
                                                <input type="hidden" name="delete_section_id" value="<?php echo $section['id']; ?>">
                                                <p>Are you sure you want to delete this section?</p>

                                                <button type="submit" name="delete_section" class="btn btn-outline-danger" style="width: 100%;">Delete</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">No sections found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
    // Search functionality
    document.getElementById('searchInput').addEventListener('input', function() {
        const filter = this.value.toLowerCase(); // Get the input value and convert to lowercase
        const rows = document.querySelectorAll('#tableBody tr'); // Get all rows in the table body

        rows.forEach(row => {
            const cells = row.querySelectorAll('td'); // Get all cells in the row
            const match = Array.from(cells).some(cell => cell.textContent.toLowerCase().includes(filter));
            row.style.display = match ? '' : 'none'; // Show or hide the row based on the match
        });
    });
</script>
<?php
include "toast.php";
include "footer.php";
?>