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
function calculateAge($birthdate)
{
    $today = new DateTime();
    $dob = new DateTime($birthdate);
    $age = $today->diff($dob)->y;
    return $age;
}
// Ensure required data is provided
if (!isset($_GET['offender_id']) || !isset($_GET['victim_id'])) {
    // Redirect if data is missing
    header('Location: admin-s-2.php');
    exit;
}

// Fetch posted data
$offender_id = intval($_GET['offender_id']);
$victim_id = intval($_GET['victim_id']);

$sql_victim = "SELECT s.first_name AS victim_first_name, 
                      s.middle_name, 
                      s.last_name AS victim_last_name, 
                      s.birthdate, s.sex,
                      s.contact_number AS contact,
                      sec.section_name, 
                      sec.grade_level, 
                      t.first_name AS teacher_fname, 
                      t.last_name AS teacher_lname,
                      m.name AS mother_name, 
                      m.contact_number AS mother_contact, 
                      m.occupation AS mother_occupation, 
                      m.address AS mother_address,
                      f.name AS father_name, 
                      f.contact_number AS father_contact, 
                      f.occupation AS father_occupation, 
                      f.address AS father_address
              FROM students s
              INNER JOIN sections sec ON s.section_id = sec.id
              LEFT JOIN teachers t ON sec.teacher_id = t.id
              LEFT JOIN mothers m ON s.id = m.student_id
              LEFT JOIN fathers f ON s.id = f.student_id
              WHERE s.id = ?";

$stmt_victim = $conn->prepare($sql_victim);
$stmt_victim->bind_param("i", $victim_id);
$stmt_victim->execute();
$result_victim = $stmt_victim->get_result();

// Check if the query was successful and fetched data
if ($result_victim === false) {
    echo "Error: " . $conn->error;
    exit;
} elseif ($result_victim->num_rows > 0) {
    $row = $result_victim->fetch_assoc();
    $victim_first_name = $row['victim_first_name'] ?? '';
    $victim_middle_name = $row['middle_name'] ?? '';
    $victim_last_name = $row['victim_last_name'] ?? '';
    $victim_birthdate = $row['birthdate'] ?? '';
    $victim_sex = $row['sex'] ?? '';
    $victim_contact = $row['contact'] ?? '';
    $victim_section_name = $row['section_name'] ?? '';
    $victim_teacher_name = ($row['teacher_fname'] ?? '') . ' ' . ($row['teacher_lname'] ?? '');
    $victim_grade_name = $row['grade_level'] ?? '';
    $victim_mother_name = $row['mother_name'] ?? '';
    $victim_mother_contact = $row['mother_contact'] ?? '';
    $victim_mother_occupation = $row['mother_occupation'] ?? '';
    $victim_mother_address = $row['mother_address'] ?? '';
    $victim_father_name = $row['father_name'] ?? '';
    $victim_father_contact = $row['father_contact'] ?? '';
    $victim_father_occupation = $row['father_occupation'] ?? '';
    $victim_father_address = $row['father_address'] ?? '';
} else {
    echo "No victim data found for victim_id: $victim_id";
    exit;
}

// Fetch offender details from database
$sql_offender = "SELECT s.first_name AS offender_first_name, 
                        s.middle_name, 
                        s.last_name AS offender_last_name, 
                        s.birthdate, s.sex, 
                        s.contact_number AS contact,
                        sec.section_name AS offender_section_name,
                        sec.grade_level AS offender_grade_level,
                        t.first_name AS teacher_fname, 
                        t.last_name AS teacher_lname,
                        m.name AS offender_mother_name, 
                        m.contact_number AS offender_mother_contact, 
                        m.occupation AS offender_mother_occupation, 
                        m.address AS offender_mother_address,
                        f.name AS offender_father_name, 
                        f.contact_number AS offender_father_contact, 
                        f.occupation AS offender_father_occupation, 
                        f.address AS offender_father_address
                FROM students s
                LEFT JOIN sections sec ON s.section_id = sec.id
                LEFT JOIN teachers t ON sec.teacher_id = t.id
                LEFT JOIN mothers m ON s.id = m.student_id
                LEFT JOIN fathers f ON s.id = f.student_id
                WHERE s.id = ?";

$stmt_offender = $conn->prepare($sql_offender);
$stmt_offender->bind_param("i", $offender_id);
$stmt_offender->execute();
$result_offender = $stmt_offender->get_result();

// Check if the query was successful and fetched data
if ($result_offender === false) {
    echo "Error: " . $conn->error;
    exit;
} elseif ($result_offender->num_rows > 0) {
    $person = $result_offender->fetch_assoc();
    $offender_first_name = $person['offender_first_name'] ?? '';
    $offender_middle_name = $person['middle_name'] ?? '';
    $offender_last_name = $person['offender_last_name'] ?? '';
    $offender_birthdate = $person['birthdate'] ?? '';
    $offender_sex = $person['sex'] ?? '';
    $offender_contact = $person['contact'] ?? '';
    $offender_grade_level = $person['offender_grade_level'] ?? '';
    $offender_section_name = $person['offender_section_name'] ?? '';
    $offender_teacher_name = ($person['teacher_fname'] ?? '') . ' ' . ($person['teacher_lname'] ?? '');
    $offender_contact = $person['contact_number'] ?? '';
    $offender_address = $person['address'] ?? '';
    $offender_mother_name = $person['offender_mother_name'] ?? '';
    $offender_mother_contact = $person['offender_mother_contact'] ?? '';
    $offender_mother_occupation = $person['offender_mother_occupation'] ?? '';
    $offender_mother_address = $person['offender_mother_address'] ?? '';
    $offender_father_name = $person['offender_father_name'] ?? '';
    $offender_father_contact = $person['offender_father_contact'] ?? '';
    $offender_father_occupation = $person['offender_father_occupation'] ?? '';
    $offender_father_address = $person['offender_father_address'] ?? '';
} else {
    echo "No offender data found for offender_id: $offender_id";
    exit;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['caseDetails'])) {
    // Fetch and sanitize POST data
    $complainantFirstName = $_POST['complainantFirstName'] ?? '';
    $complainantMiddleName = $_POST['complainantMiddleName'] ?? '';
    $complainantLastName = $_POST['complainantLastName'] ?? '';
    $relationshipToVictim = $_POST['relationshipToVictim'] ?? '';
    $complainantContact = $_POST['complainantContact'] ?? '';
    $complainantAddress = $_POST['complainantAddress'] ?? '';

    $caseDetails = $_POST['caseDetails'] ?? '';
    $actionTaken = $_POST['actionTaken'] ?? '';
    $recommendations = $_POST['recommendations'] ?? '';
    $reportedAt = $_POST['reportedAt'] ?? '';

    // Calculate age of victim and offender based on birthdates
    $victimAge = calculateAge($victim_birthdate);
    $offenderAge = calculateAge($offender_birthdate);

    // Prepare the SQL query directly
    $sql_insert = "INSERT INTO complaints_student (
    victimFirstName, victimMiddleName, victimLastName, victimDOB, victimAge, victimSex, victimGrade, victimSection, victimAdviser, victimContact,
    motherName, motherOccupation, motherAddress, motherContact,
    fatherName, fatherOccupation, fatherAddress, fatherContact,
    complainantFirstName, complainantMiddleName, complainantLastName, relationshipToVictim, complainantContact, complainantAddress,
    complainedFirstName, complainedMiddleName, complainedLastName, complainedDOB, complainedAge, complainedSex, complainedGrade, complainedSection, complainedAdviser, complainedContact,
    complainedMotherName, complainedMotherOccupation, complainedMotherAddress, complainedMotherContact,
    complainedFatherName, complainedFatherOccupation, complainedFatherAddress, complainedFatherContact,
    caseDetails, actionTaken, recommendations, reportedAt, student_id
) VALUES (
    '$victim_first_name', '$victim_middle_name', '$victim_last_name', '$victim_birthdate', '$victimAge', '$victim_sex',
    '$victim_grade_name', '$victim_section_name', '$victim_teacher_name', '$victim_contact',
    '$victim_mother_name', '$victim_mother_occupation', '$victim_mother_address', '$victim_mother_contact',
    '$victim_father_name', '$victim_father_occupation', '$victim_father_address', '$victim_father_contact',
    '$complainantFirstName', '$complainantMiddleName', '$complainantLastName', '$relationshipToVictim', '$complainantContact', '$complainantAddress',
    '$offender_first_name', '$offender_middle_name', '$offender_last_name', '$offender_birthdate', '$offenderAge', '$offender_sex', '$offender_grade_level', '$offender_section_name', '$offender_teacher_name', '$offender_contact',
    '$offender_mother_name', '$offender_mother_occupation', '$offender_mother_address', '$offender_mother_contact',
    '$offender_father_name', '$offender_father_occupation', '$offender_father_address', '$offender_father_contact',
    '$caseDetails', '$actionTaken', '$recommendations', '$reportedAt', '$offender_id)'
)";

    // Execute the query
    if ($conn->query($sql_insert) === TRUE) {
        header("Location: complaint_student.php");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}

?>
<div id="main">

    <?php include "header.php"; ?>
    <div class="container-fluid mb-2">
        <div class="container-fluid bg-white mt-2 rounded-lg border">
            <div class="row pt-3">
                <div class="col-md-6">
                    <div class="container-fluid">
                        <h3 class="p-2"><strong>
                                COMPLAINT DETAILS</strong>
                        </h3>
                    </div>
                </div>
            </div>

            <div class="container-fluid bg-white rounded-lg">
                <form action="" method="post">
                    <h5 class="text-center bg-custom text-white p-2 rounded-lg"><b>Victim Details</b></h5>
                    <input type="hidden" name="reportedAt" value="<?php echo date('Y-m-d H:i:s'); ?>">
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <strong>First Name:</strong>
                            <input type="text" class="form-control" value="<?php echo ucwords(htmlspecialchars($victim_first_name)); ?>" required readonly>
                        </div>
                        <div class="col-md-4">
                            <strong>Middle Name:</strong>
                            <input type="text" class="form-control" value="<?php echo ucwords(htmlspecialchars($victim_middle_name)); ?>" readonly>
                        </div>
                        <div class="col-md-4">
                            <strong>Last Name:</strong>
                            <input type="text" class="form-control" value="<?php echo ucwords(htmlspecialchars($victim_last_name)); ?>" required readonly>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-8"><strong>Date of Birth:</strong>
                            <input type="date" class="form-control" name="birthdate" value="<?php echo htmlspecialchars($victim_birthdate); ?>" required readonly>
                        </div>

                        <div class="col-md-4">
                            <strong>Sex:</strong>
                            <select class="form-control" name="victim_sex" required readonly>
                                <option value="Male" <?php echo ($victim_sex == 'Male') ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo ($victim_sex == 'Female') ? 'selected' : ''; ?>>Female</option>
                            </select>
                        </div>


                    </div>

                    <div class="row mt-3 pb-4">
                        <div class="col-md-3"><strong>Grade:</strong>
                            <input type="text" class="form-control" value="<?php echo ucwords(htmlspecialchars($victim_grade_name)); ?>" required readonly>
                        </div>
                        <div class="col-md-3"><strong>Section:</strong>
                            <input type="text" class="form-control" value="<?php echo ucwords(htmlspecialchars($victim_section_name)); ?>" required readonly>
                        </div>
                        <div class="col-md-6"><strong>Adviser:</strong>
                            <input type="text" class="form-control" value="<?php echo ucwords(htmlspecialchars($victim_teacher_name)); ?>" required readonly>
                        </div>

                    </div>
                    <hr>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="container-fluid">
                                <h5 class="text-center"><b>Mother</b></h5>
                                <div class="row">
                                    <strong>Name:</strong>
                                    <input type="text" class="form-control" name="motherName" value="<?php echo ucwords(htmlspecialchars($victim_mother_name)); ?>" required readonly>
                                </div>
                                <div class="row mt-3"><strong>Occupation:</strong>

                                    <input type="text" class="form-control" name="motherOccupation" value="<?php echo ucwords(htmlspecialchars($victim_mother_occupation)); ?>" required readonly>
                                </div>
                                <div class="row mt-3"><strong>Address:</strong>

                                    <input type="text" class="form-control" name="motherAddress" value="<?php echo ucwords(htmlspecialchars($victim_mother_address)); ?>" required readonly>
                                </div>
                                <div class="row mt-3"><strong>Contact:</strong>

                                    <input type="text" class="form-control" name="motherContact" value="<?php echo htmlspecialchars($victim_mother_contact); ?>"
                                        required readonly
                                        pattern="09\d{9}"
                                        maxlength="11"
                                        title="Please enter a valid 11-digit number starting with 09."
                                        value="09"
                                        oninput="if (!this.value.startsWith('09')) this.value = '09' + this.value.replace(/^09*/, ''); this.value = this.value.replace(/\D/g, '');">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="container-fluid">
                                <h5 class="text-center"><b>Father</b></h5>
                                <div class="row">
                                    <strong>Name:</strong>
                                    <input type="text" class="form-control" name="fatherName" value="<?php echo ucwords(htmlspecialchars($victim_father_name)); ?>" required readonly>
                                </div>
                                <div class="row mt-3"><strong>Occupation:</strong>

                                    <input type="text" class="form-control" name="fatherOccupation" value="<?php echo ucwords(htmlspecialchars($victim_father_occupation)); ?>" required readonly>
                                </div>
                                <div class="row mt-3"><strong>Address:</strong>

                                    <input type="text" class="form-control" name="fatherAddress" value="<?php echo ucwords(htmlspecialchars($victim_father_address)); ?>" required readonly>
                                </div>
                                <div class="row mt-3"><strong>Contact:</strong>

                                    <input type="text" class="form-control" name="fatherContact" value="<?php echo htmlspecialchars($victim_father_contact); ?>" required readonly
                                        pattern="09\d{9}"
                                        maxlength="11"
                                        title="Please enter a valid 11-digit number starting with 09."
                                        value="09"
                                        oninput="if (!this.value.startsWith('09')) this.value = '09' + this.value.replace(/^09*/, ''); this.value = this.value.replace(/\D/g, '');">
                                </div>
                            </div>
                        </div>
                    </div>
            </div>

            <div class="container-fluid bg-white pt-4 mt-2 rounded-lg">
                <h5 class="text-center bg-custom text-white p-2 rounded-lg"><b>Offender Details</b></h5>
                <div class="row mt-3">
                    <div class="col-md-4">
                        <strong>First Name:</strong>
                        <input type="text" class="form-control" value="<?php echo ucwords(htmlspecialchars($offender_first_name)); ?>" required readonly>
                    </div>
                    <div class="col-md-4">
                        <strong>Middle Name:</strong>
                        <input type="text" class="form-control" value="<?php echo ucwords(htmlspecialchars($offender_middle_name)); ?>" required readonly>
                    </div>
                    <div class="col-md-4">
                        <strong>Last Name:</strong>
                        <input type="text" class="form-control" value="<?php echo ucwords(htmlspecialchars($offender_last_name)); ?>" required readonly>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-8"><strong>Date of Birth:</strong>
                        <input type="date" class="form-control" name="offender_birthdate" value="<?php echo htmlspecialchars($offender_birthdate); ?>" required readonly>
                    </div>

                    <div class="col-md-4">
                        <strong>Sex:</strong>
                        <select class="form-control" name="offender_sex" required readonly>
                            <option value="Male" <?php echo ($offender_sex == 'Male') ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo ($offender_sex == 'Female') ? 'selected' : ''; ?>>Female</option>
                        </select>
                    </div>
                </div>

                <div class="row mt-3 pb-4">
                    <div class="col-md-3"><strong>Grade:</strong>
                        <input type="text" class="form-control" value="<?php echo ucwords(htmlspecialchars($offender_grade_level)); ?>" required readonly>
                    </div>
                    <div class="col-md-3"><strong>Section:</strong>
                        <input type="text" class="form-control" value="<?php echo ucwords(htmlspecialchars($offender_section_name)); ?>" required readonly>
                    </div>
                    <div class="col-md-6"><strong>Adviser:</strong>
                        <input type="text" class="form-control" value="<?php echo ucwords(htmlspecialchars($offender_teacher_name)); ?>" required readonly>
                    </div>

                </div>
                <hr>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="container-fluid">
                            <h5 class="text-center"><b>Mother</b></h5>
                            <div class="row">
                                <strong>Name:</strong>
                                <input type="text" class="form-control" name="offenderMotherName" value="<?php echo ucwords(htmlspecialchars($offender_mother_name)); ?>" required readonly>
                            </div>
                            <div class="row mt-3"><strong>Occupation:</strong>

                                <input type="text" class="form-control" name="offenderMotherOccupation" value="<?php echo ucwords(htmlspecialchars($offender_mother_occupation)); ?>" required readonly>
                            </div>
                            <div class="row mt-3"><strong>Address:</strong>

                                <input type="text" class="form-control" name="offenderMotherAddress" value="<?php echo ucwords(htmlspecialchars($offender_mother_address)); ?>" required readonly>
                            </div>
                            <div class="row mt-3"><strong>Contact:</strong>

                                <input type="text" class="form-control" name="offenderMotherContact" value="<?php echo htmlspecialchars($offender_mother_contact); ?>" required readonly
                                    pattern="09\d{9}"
                                    maxlength="11"
                                    title="Please enter a valid 11-digit number starting with 09."
                                    value="09"
                                    oninput="if (!this.value.startsWith('09')) this.value = '09' + this.value.replace(/^09*/, ''); this.value = this.value.replace(/\D/g, '');">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="container-fluid">
                            <h5 class="text-center"><b>Father</b></h5>
                            <div class="row">
                                <strong>Name:</strong>
                                <input type="text" class="form-control" name="offenderFatherName" value="<?php echo ucwords(htmlspecialchars($offender_father_name)); ?>" required readonly>
                            </div>
                            <div class="row mt-3"><strong>Occupation:</strong>

                                <input type="text" class="form-control" name="offenderFatherOccupation" value="<?php echo ucwords(htmlspecialchars($offender_father_occupation)); ?>" required readonly>
                            </div>
                            <div class="row mt-3"><strong>Address:</strong>

                                <input type="text" class="form-control" name="offenderFatherAddress" value="<?php echo ucwords(htmlspecialchars($offender_father_address)); ?>" required readonly>
                            </div>
                            <div class="row mt-3"><strong>Contact:</strong>

                                <input type="text" class="form-control" name="offenderFatherContact" value="<?php echo htmlspecialchars($offender_father_contact); ?>" required readonly
                                    pattern="09\d{9}"
                                    maxlength="11"
                                    title="Please enter a valid 11-digit number starting with 09."
                                    value="09"
                                    oninput="if (!this.value.startsWith('09')) this.value = '09' + this.value.replace(/^09*/, ''); this.value = this.value.replace(/\D/g, '');">
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="container-fluid bg-white p-4 rounded-lg mt-4">
                <h5 class="text-center bg-custom text-white p-2 rounded-lg"><b>Complainant Details</b></h5>
                <div class="form-row mt-3">
                    <div class="form-group col-md-4">
                        <label for="complainantFirstName">First Name:</label>
                        <input type="text" class="form-control" id="complainantFirstName" name="complainantFirstName" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="complainantMiddleName">Middle Name:</label>
                        <input type="text" class="form-control" id="complainantMiddleName" name="complainantMiddleName" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="complainantLastName">Last Name:</label>
                        <input type="text" class="form-control" id="complainantLastName" name="complainantLastName" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="relationshipToVictim">Relationship to Victim:</label>
                    <input type="text" class="form-control" id="relationshipToVictim" name="relationshipToVictim" required>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="complainantContact">Contact Number:</label>
                        <input type="text" class="form-control" id="complainantContact" name="complainantContact" required
                            pattern="09\d{9}"
                            maxlength="11"
                            title="Please enter a valid 11-digit number starting with 09."
                            value="09"
                            oninput="if (!this.value.startsWith('09')) this.value = '09' + this.value.replace(/^09*/, ''); this.value = this.value.replace(/\D/g, '');">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="complainantAddress">Address:</label>
                        <input type="text" class="form-control" id="complainantAddress" name="complainantAddress" required>
                    </div>
                </div>
            </div>

            <div class="container-fluid bg-white p-4 rounded-lg mt-4">
                <h5 class="text-center bg-custom text-white p-2 rounded-lg"><b>Details of the Case</b></h5>
                <div class="form-group">
                    <textarea class="form-control" id="caseDetails" name="caseDetails" rows="5" placeholder="Enter the Details of the case..." required></textarea>
                </div>

                <h5 class="text-center bg-custom text-white p-2 rounded-lg"><b>Action Taken</b></h5>
                <div class="form-group">
                    <textarea class="form-control" id="actionTaken" name="actionTaken" rows="5" placeholder="Enter the Details of the action taken by the school..." required></textarea>
                </div>

                <h5 class="text-center bg-custom text-white p-2 rounded-lg"><b>Recommendations</b></h5>
                <div class="form-group">
                    <textarea class="form-control" id="recommendations" name="recommendations" rows="5" placeholder="Enter the recommendation of the school..." required></textarea>
                </div>
            </div>
            <button type="submit" class="btn btn-success" style="width: 100%; margin: 5px">Finish</button>

            </form>
        </div>
    </div>
</div>

<?php
include "toast.php";
include "footer.php";
?>