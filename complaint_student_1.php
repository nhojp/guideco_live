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
// Initialize variables
$complaint_data = [];

if (isset($_GET['id'])) {
    $complaint_id = $_GET['id'];

    $sql = "SELECT * FROM complaints_student WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("i", $complaint_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $complaint_data = $result->fetch_assoc();
        } else {
            echo "No complaint found for the given ID.";
        }
    } else {
        echo "Failed to prepare statement: " . $conn->error;
    }

    $stmt->close();
} else {
    echo "No complaint ID provided.";
}

if (isset($_POST['approve'])) {
    // Get the complaint ID
    $complaint_id = $_GET['id']; // Assuming the ID is passed via the URL
    $victimAge = calculateAge($_POST['victimDOB']);
    $complainedAge = calculateAge($_POST['complainedDOB']);

    // Prepare the SQL to update the complaint status and details
    $sql = "UPDATE complaints_student
            SET status = 'complete',
                victimFirstName = ?, 
                victimMiddleName = ?, 
                victimLastName = ?, 
                victimDOB = ?, 
                victimAge = ?,
                victimSex = ?, 
                victimGrade = ?,
                victimSection = ?,
                victimAdviser = ?,

                motherName = ?, 
                motherOccupation = ?, 
                motherAddress = ?, 
                motherContact = ?, 
                fatherName = ?, 
                fatherOccupation = ?, 
                fatherAddress = ?, 
                fatherContact = ?,

                complainantFirstName = ?, 
                complainantMiddleName = ?, 
                complainantLastName = ?, 
                relationshipToVictim = ?,
                complainantContact = ?, 
                complainantAddress = ?, 

                complainedFirstName = ?, 
                complainedMiddleName = ?, 
                complainedLastName = ?, 
                complainedDOB = ?,
                complainedAge = ?,
                complainedSex = ?,
                complainedGrade = ?,
                complainedSection = ?,
                complainedAdviser = ?,

                complainedMotherName = ?, 
                complainedMotherOccupation = ?, 
                complainedMotherAddress = ?, 
                complainedMotherContact = ?, 
                complainedFatherName = ?, 
                complainedFatherOccupation = ?, 
                complainedFatherAddress = ?, 
                complainedFatherContact = ?,
                
                caseDetails = ?, 
                actionTaken = ?, 
                recommendations = ?
            WHERE id = ?";

    if ($stmt = $conn->prepare($sql)) {
        // Bind parameters to the statement
        $stmt->bind_param(
            "ssssissssssssssssssssssssssisssssssssssssssi",
            $_POST['victimFirstName'],
            $_POST['victimMiddleName'],
            $_POST['victimLastName'],
            $_POST['victimDOB'],
            $victimAge,
            $_POST['victimSex'],
            $_POST['victimGrade'],
            $_POST['victimSection'],
            $_POST['victimAdviser'],

            $_POST['motherName'],
            $_POST['motherOccupation'],
            $_POST['motherAddress'],
            $_POST['motherContact'],
            $_POST['fatherName'],
            $_POST['fatherOccupation'],
            $_POST['fatherAddress'],
            $_POST['fatherContact'],

            $_POST['complainantFirstName'],
            $_POST['complainantMiddleName'],
            $_POST['complainantLastName'],
            $_POST['relationshipToVictim'],
            $_POST['complainantContact'],
            $_POST['complainantAddress'],

            $_POST['complainedFirstName'],
            $_POST['complainedMiddleName'],
            $_POST['complainedLastName'],
            $_POST['complainedDOB'],
            $complainedAge,
            $_POST['complainedSex'],
            $_POST['complainedGrade'],
            $_POST['complainedSection'],
            $_POST['complainedAdviser'],

            $_POST['complainedMotherName'],
            $_POST['complainedMotherOccupation'],
            $_POST['complainedMotherAddress'],
            $_POST['complainedMotherContact'],
            $_POST['complainedFatherName'],
            $_POST['complainedFatherOccupation'],
            $_POST['complainedFatherAddress'],
            $_POST['complainedFatherContact'],

            $_POST['caseDetails'],
            $_POST['actionTaken'],
            $_POST['recommendations'],
            $complaint_id // Integer
        );

        if ($stmt->execute()) {
            // Debug: Confirm query success
            error_log("Query executed successfully!");

            $_SESSION['toast_message'] = "Approved succesfully!";
            $_SESSION['toast_class'] = "success";  // Bootstrap success class

            // Redirect
            header("Location: complaint_teacher.php");
            exit();
        } else {
            // Debug: Confirm query success
            error_log("Query executed successfully!");

            $_SESSION['toast_message'] = "Approving failed!";
            $_SESSION['toast_class'] = "failed";  // Bootstrap success class

            // Redirect
            header("Location: complaint_teacher_1.php");
            exit();
        }

        // Close the statement
        $stmt->close();
    } else {
        echo "Failed to prepare statement: " . $conn->error;
    }
}

// Handle "Save" button
if (isset($_POST['save'])) {
    // Get the complaint ID
    $complaint_id = $_GET['id']; // Assuming the ID is passed via the URL
    $victimAge = calculateAge($_POST['victimDOB']);
    $complainedAge = calculateAge($_POST['complainedDOB']);

    // Prepare the SQL to update the complaint status and details with "pending"
    $sql = "UPDATE complaints_student
            SET status = 'pending', 
                victimFirstName = ?, 
                victimMiddleName = ?, 
                victimLastName = ?, 
                victimDOB = ?, 
                victimAge = ?,
                victimSex = ?, 
                victimGrade = ?,
                victimSection = ?,
                victimAdviser = ?,

                motherName = ?, 
                motherOccupation = ?, 
                motherAddress = ?, 
                motherContact = ?, 
                fatherName = ?, 
                fatherOccupation = ?, 
                fatherAddress = ?, 
                fatherContact = ?, 

                complainantFirstName = ?, 
                complainantMiddleName = ?, 
                complainantLastName = ?, 
                relationshipToVictim = ?,
                complainantContact = ?, 
                complainantAddress = ?, 

                complainedFirstName = ?, 
                complainedMiddleName = ?, 
                complainedLastName = ?, 
                complainedDOB = ?,
                complainedAge = ?,
                complainedSex = ?,
                complainedGrade = ?,
                complainedSection = ?,
                complainedAdviser = ?,

                complainedMotherName = ?, 
                complainedMotherOccupation = ?, 
                complainedMotherAddress = ?, 
                complainedMotherContact = ?, 
                complainedFatherName = ?, 
                complainedFatherOccupation = ?, 
                complainedFatherAddress = ?, 
                complainedFatherContact = ?,

                caseDetails = ?, 
                actionTaken = ?, 
                recommendations = ?
            WHERE id = ?";

    if ($stmt = $conn->prepare($sql)) {
        // Bind parameters to the statement
        $stmt->bind_param(
            "ssssissssssssssssssssssssssisssssssssssssssi",
            $_POST['victimFirstName'],
            $_POST['victimMiddleName'],
            $_POST['victimLastName'],
            $_POST['victimDOB'],
            $victimAge,
            $_POST['victimSex'],
            $_POST['victimGrade'],
            $_POST['victimSection'],
            $_POST['victimAdviser'],

            $_POST['motherName'],
            $_POST['motherOccupation'],
            $_POST['motherAddress'],
            $_POST['motherContact'],
            $_POST['fatherName'],
            $_POST['fatherOccupation'],
            $_POST['fatherAddress'],
            $_POST['fatherContact'],

            $_POST['complainantFirstName'],
            $_POST['complainantMiddleName'],
            $_POST['complainantLastName'],
            $_POST['relationshipToVictim'],
            $_POST['complainantContact'],
            $_POST['complainantAddress'],

            $_POST['complainedFirstName'],
            $_POST['complainedMiddleName'],
            $_POST['complainedLastName'],
            $_POST['complainedDOB'],
            $complainedAge,
            $_POST['complainedSex'],
            $_POST['complainedGrade'],
            $_POST['complainedSection'],
            $_POST['complainedAdviser'],

            $_POST['complainedMotherName'],
            $_POST['complainedMotherOccupation'],
            $_POST['complainedMotherAddress'],
            $_POST['complainedMotherContact'],
            $_POST['complainedFatherName'],
            $_POST['complainedFatherOccupation'],
            $_POST['complainedFatherAddress'],
            $_POST['complainedFatherContact'],

            $_POST['caseDetails'],
            $_POST['actionTaken'],
            $_POST['recommendations'],
            $complaint_id
        );

        if ($stmt->execute()) {
            // Debug: Confirm query success
            error_log("Query executed successfully!");

            $_SESSION['toast_message'] = "Saved changes!";
            $_SESSION['toast_class'] = "success";  // Bootstrap success class

            // Redirect
            header("Location: complaint_teacher.php");
            exit();
        } else {
            // Debug: Confirm query success
            error_log("Query executed successfully!");

            $_SESSION['toast_message'] = "Saving failed!";
            $_SESSION['toast_class'] = "failed";  // Bootstrap success class

            // Redirect
            header("Location: complaint_teacher_1.php");
            exit();
        }

        // Close the statement
        $stmt->close();
    } else {
        echo "Failed to prepare statement: " . $conn->error;
    }
}

// Handle "Discard" button
if (isset($_POST['discard'])) {
    // Get the complaint ID
    $complaint_id = $_GET['id'];

    // Prepare the SQL to delete the complaint
    $sql = "DELETE FROM complaints_student WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $complaint_id);

        if ($stmt->execute()) {
            // Debug: Confirm query success
            error_log("Query executed successfully!");

            $_SESSION['toast_message'] = "Discard complete!";
            $_SESSION['toast_class'] = "success";  // Bootstrap success class

            // Redirect
            header("Location: complaint_teacher.php");
            exit();
        } else {
            // Debug: Confirm query success
            error_log("Query executed successfully!");

            $_SESSION['toast_message'] = "Discard failed!";
            $_SESSION['toast_class'] = "failed";  // Bootstrap success class

            // Redirect
            header("Location: complaint_teacher_1.php");
            exit();
        }

        // Close the statement
        $stmt->close();
    } else {
        echo "Failed to prepare statement: " . $conn->error;
    }
}

?>
<div id="main">

    <?php include "header.php"; ?>

    <div class="container-fluid mb-2">
        <div class="container-fluid bg-white mt-2 mb-2 rounded-lg border">
            <div class="row pt-3">
                <div class="col-md-6">
                    <h3 class="p-2"><strong>COMPLAINT DETAILS</strong></h3>
                </div>
            </div>
        </div>

        <!-- Wrap the entire form in one tag -->
        <form action="" method="post">
            <div class="container-fluid bg-white mt-2 rounded-lg border">
                <h5 class="text-center mt-3 text-success"><strong>Victim Details</strong></h5>
                <div class="form-row mt-3">
                    <div class="col-md-4">
                        <strong>First Name:</strong>
                        <input type="text" class="form-control" name="victimFirstName"
                            value="<?php echo htmlspecialchars($complaint_data['victimFirstName'] ?? ''); ?>">
                    </div>
                    <div class="col-md-4">
                        <strong>Middle Initial:</strong>
                        <input type="text" class="form-control" name="victimMiddleName"
                            value="<?php echo htmlspecialchars($complaint_data['victimMiddleName'] ?? ''); ?>">
                    </div>
                    <div class="col-md-4">
                        <strong>Last Name:</strong>
                        <input type="text" class="form-control" name="victimLastName"
                            value="<?php echo htmlspecialchars($complaint_data['victimLastName'] ?? ''); ?>">
                    </div>
                </div>
                <div class="form-row mt-3">
                    <div class="col-md-6">
                        <strong>Date of Birth:</strong>
                        <input type="date" class="form-control" name="victimDOB"
                            value="<?php echo htmlspecialchars($complaint_data['victimDOB'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6">
                        <strong>Sex:</strong>
                        <input type="text" class="form-control" name="victimSex"
                            value="<?php echo htmlspecialchars($complaint_data['victimSex'] ?? ''); ?>">
                    </div>
                </div>
                <div class="form-row mt-3 pb-4">
                    <div class="col-md-3"><strong>Grade:</strong>
                        <input type="text" class="form-control" name="victimGrade"
                            value="<?php echo htmlspecialchars($complaint_data['victimGrade'] ?? ''); ?>">
                    </div>
                    <div class="col-md-3"><strong>Section:</strong>
                        <input type="text" class="form-control" name="victimSection"
                            value="<?php echo htmlspecialchars($complaint_data['victimSection'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6"><strong>Adviser:</strong>
                        <input type="text" class="form-control" name="victimAdviser"
                            value="<?php echo htmlspecialchars(ucwords($complaint_data['victimAdviser']) ?? ''); ?>">
                    </div>

                </div>

                <div class="form-row mt-3 pb-3">
                    <div class="col-md-6">
                        <div class="container-fluid">
                            <h5 class="text-center mt-3 text-success"><strong>Mother</strong></h5>
                            <div class="row">
                                <strong>Name:</strong>
                                <input type="text" class="form-control" name="motherName"
                                    value="<?php echo ucwords(htmlspecialchars($complaint_data['motherName'] ?? '')); ?>">
                            </div>
                            <div class="row mt-3">
                                <strong>Occupation:</strong>
                                <input type="text" class="form-control" name="motherOccupation"
                                    value="<?php echo ucwords(htmlspecialchars($complaint_data['motherOccupation'] ?? '')); ?>">
                            </div>
                            <div class="row mt-3">
                                <strong>Address:</strong>
                                <input type="text" class="form-control" name="motherAddress"
                                    value="<?php echo ucwords(htmlspecialchars($complaint_data['motherAddress'] ?? '')); ?>">
                            </div>
                            <div class="row mt-3">
                                <strong>Contact:</strong>
                                <input type="text" class="form-control" name="motherContact"
                                    value="<?php echo htmlspecialchars($complaint_data['motherContact'] ?? ''); ?>"
                                    required
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
                            <h5 class="text-center mt-3 text-success"><strong>Father</strong></h5>
                            <div class="row">
                                <strong>Name:</strong>
                                <input type="text" class="form-control" name="fatherName"
                                    value="<?php echo ucwords(htmlspecialchars($complaint_data['fatherName'] ?? '')); ?>">
                            </div>
                            <div class="row mt-3">
                                <strong>Occupation:</strong>
                                <input type="text" class="form-control" name="fatherOccupation"
                                    value="<?php echo ucwords(htmlspecialchars($complaint_data['fatherOccupation'] ?? '')); ?>">
                            </div>
                            <div class="row mt-3">
                                <strong>Address:</strong>
                                <input type="text" class="form-control" name="fatherAddress"
                                    value="<?php echo ucwords(htmlspecialchars($complaint_data['fatherAddress'] ?? '')); ?>">
                            </div>
                            <div class="row mt-3">
                                <strong>Contact:</strong>
                                <input type="text" class="form-control" name="fatherContact"
                                    value="<?php echo htmlspecialchars($complaint_data['fatherContact'] ?? ''); ?>"
                                    required
                                    pattern="09\d{9}"
                                    maxlength="11"
                                    title="Please enter a valid 11-digit number starting with 09."
                                    value="09"
                                    oninput="if (!this.value.startsWith('09')) this.value = '09' + this.value.replace(/^09*/, ''); this.value = this.value.replace(/\D/g, '');">
                            </div>
                        </div>
                    </div>
                </div>

                <hr>

                <h5 class="text-center mt-3 text-success"><strong>Complained Details</strong></h5>
                <div class="form-row mt-3">
                    <div class="col-md-4">
                        <strong>First Name:</strong>
                        <input type="text" class="form-control" name="complainedFirstName"
                            value="<?php echo ucwords(htmlspecialchars($complaint_data['complainedFirstName'] ?? '')); ?>">
                    </div>
                    <div class="col-md-4">
                        <strong>Middle Name:</strong>
                        <input type="text" class="form-control" name="complainedMiddleName"
                            value="<?php echo htmlspecialchars(ucwords($complaint_data['complainedMiddleName']) ?? ''); ?>">
                    </div>
                    <div class="col-md-4">
                        <strong>Last Name:</strong>
                        <input type="text" class="form-control" name="complainedLastName"
                            value="<?php echo htmlspecialchars($complaint_data['complainedLastName'] ?? ''); ?>">
                    </div>
                </div>
                <div class="form-row mt-3">
                    <div class="col-md-8">
                        <strong>Date of Birth</strong>
                        <input type="text" class="form-control" name="complainedDOB"
                            value="<?php echo htmlspecialchars($complaint_data['complainedDOB'] ?? ''); ?>">
                    </div>
                    <div class="col-md-4">
                        <strong>Sex</strong>
                        <input type="text" class="form-control" name="complainedAge"
                            value="<?php echo htmlspecialchars($complaint_data['complainedAge'] ?? ''); ?>">
                    </div>
                </div>
                <div class="form-row mt-3 pb-4">
                    <div class="col-md-3"><strong>Grade:</strong>
                        <input type="text" class="form-control" name="complainedGrade"
                            value="<?php echo htmlspecialchars($complaint_data['complainedGrade'] ?? ''); ?>">
                    </div>
                    <div class="col-md-3"><strong>Section:</strong>
                        <input type="text" class="form-control" name="complainedSection"
                            value="<?php echo htmlspecialchars($complaint_data['complainedSection'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6"><strong>Adviser:</strong>
                        <input type="text" class="form-control" name="complainedAdviser"
                            value="<?php echo htmlspecialchars(ucwords($complaint_data['complainedAdviser']) ?? ''); ?>">
                    </div>

                </div>
                <div class="form-row mt-3 pb-3">
                    <div class="col-md-6">
                        <div class="container-fluid">
                            <h5 class="text-center text-success"><b>Mother</b></h5>
                            <div class="row">
                                <strong>Name:</strong>
                                <input type="text" class="form-control" name="complainedMotherName"
                                    value="<?php echo ucwords(htmlspecialchars($complaint_data['complainedMotherName'] ?? '')); ?>">
                            </div>
                            <div class="row mt-3">
                                <strong>Occupation:</strong>
                                <input type="text" class="form-control" name="complainedMotherOccupation"
                                    value="<?php echo ucwords(htmlspecialchars($complaint_data['complainedMotherOccupation'] ?? '')); ?>">
                            </div>
                            <div class="row mt-3">
                                <strong>Address:</strong>
                                <input type="text" class="form-control" name="complainedMotherAddress"
                                    value="<?php echo ucwords(htmlspecialchars($complaint_data['complainedMotherAddress'] ?? '')); ?>">
                            </div>
                            <div class="row mt-3">
                                <strong>Contact:</strong>
                                <input type="text" class="form-control" name="complainedMotherContact"
                                    value="<?php echo htmlspecialchars($complaint_data['complainedMotherContact'] ?? ''); ?>"
                                    required
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
                            <h5 class="text-center text-success"><b>Father</b></h5>
                            <div class="row">
                                <strong>Name:</strong>
                                <input type="text" class="form-control" name="complainedFatherName"
                                    value="<?php echo ucwords(htmlspecialchars($complaint_data['complainedFatherName'] ?? '')); ?>">
                            </div>
                            <div class="row mt-3">
                                <strong>Occupation:</strong>
                                <input type="text" class="form-control" name="complainedFatherOccupation"
                                    value="<?php echo ucwords(htmlspecialchars($complaint_data['complainedFatherOccupation'] ?? '')); ?>">
                            </div>
                            <div class="row mt-3">
                                <strong>Address:</strong>
                                <input type="text" class="form-control" name="complainedFatherAddress"
                                    value="<?php echo ucwords(htmlspecialchars($complaint_data['complainedFatherAddress'] ?? '')); ?>">
                            </div>
                            <div class="row mt-3">
                                <strong>Contact:</strong>
                                <input type="text" class="form-control" name="complainedFatherContact"
                                    value="<?php echo htmlspecialchars($complaint_data['complainedFatherContact'] ?? ''); ?>"
                                    required
                                    pattern="09\d{9}"
                                    maxlength="11"
                                    title="Please enter a valid 11-digit number starting with 09."
                                    value="09"
                                    oninput="if (!this.value.startsWith('09')) this.value = '09' + this.value.replace(/^09*/, ''); this.value = this.value.replace(/\D/g, '');">
                            </div>
                        </div>
                    </div>
                </div>

                <hr>

                <h5 class="text-center mt-3 text-success"><strong>Complainant Details</strong></h5>
                <div class="form-row mt-3">
                    <div class="col-md-4">
                        <strong>First Name:</strong>
                        <input type="text" class="form-control" name="complainantFirstName"
                            value="<?php echo htmlspecialchars($complaint_data['complainantFirstName'] ?? ''); ?>">
                    </div>
                    <div class="col-md-4">
                        <strong>Middle Name:</strong>
                        <input type="text" class="form-control" name="complainantMiddleName"
                            value="<?php echo htmlspecialchars($complaint_data['complainantMiddleName'] ?? ''); ?>">
                    </div>
                    <div class="col-md-4">
                        <strong>Last Name:</strong>
                        <input type="text" class="form-control" name="complainantLastName"
                            value="<?php echo htmlspecialchars($complaint_data['complainantLastName'] ?? ''); ?>">
                    </div>
                </div>
                <div class="form-row mt-3">
                    <div class="col-md-12">
                        <strong>Relationship to Victim</strong>
                        <input type="text" class="form-control" name="relationshipToVictim"
                            value="<?php echo htmlspecialchars($complaint_data['relationshipToVictim'] ?? ''); ?>">
                    </div>
                </div>
                <div class="form-row mt-3">
                    <div class="col-md-6">
                        <strong>Contact Number:</strong>
                        <input type="text" class="form-control" name="complainantContact"
                            value="<?php echo htmlspecialchars($complaint_data['complainantContact'] ?? ''); ?>"
                            required
                            pattern="09\d{9}"
                            maxlength="11"
                            title="Please enter a valid 11-digit number starting with 09."
                            value="09"
                            oninput="if (!this.value.startsWith('09')) this.value = '09' + this.value.replace(/^09*/, ''); this.value = this.value.replace(/\D/g, '');">
                    </div>
                    <div class="col-md-6">
                        <strong>Address:</strong>
                        <input type="text" class="form-control" name="complainantAddress"
                            value="<?php echo htmlspecialchars($complaint_data['complainantAddress'] ?? ''); ?>">
                    </div>
                </div>

                <hr>
                <h5 class="text-center mt-3 text-success mb-2"><strong>Case Details</strong></h5>

                <div class="form-group">
                    <label for="caseDetails"><strong>Case Details:</strong></label>
                    <textarea class="form-control" id="caseDetails" name="caseDetails" rows="5" placeholder="Enter the details of the case..."><?php echo htmlspecialchars($complaint_data['caseDetails'] ?? ''); ?></textarea>
                </div>

                <!-- Action Taken Section -->
                <div class="form-group">
                    <label for="actionTaken"><strong>Action Taken:</strong></label>
                    <textarea class="form-control" id="actionTaken" name="actionTaken" rows="5" placeholder="Enter the details of the action taken by the school..."><?php echo htmlspecialchars($complaint_data['actionTaken'] ?? ''); ?></textarea>
                </div>

                <!-- Recommendations Section -->
                <div class="form-group">
                    <label for="recommendations"><strong>Recommendations:</strong></label>
                    <textarea class="form-control" id="recommendations" name="recommendations" rows="5" placeholder="Enter the recommendations of the school..."><?php echo htmlspecialchars($complaint_data['recommendations'] ?? ''); ?></textarea>
                </div>
                <!-- Submit Button -->
                <div class="form-row mt-2 mb-2">
                    <div class="col-md-12 text-right">
                        <button type="submit" name="discard" class="btn btn-outline-danger">Discard</button>
                        <button type="submit" name="save" class="btn btn-outline-success">Save changes</button>
                        <button type="submit" class="btn btn-success" name="approve">Complete</button>
                    </div>
                </div>
            </div>

        </form>
    </div>
</div>

<?php
include "toast.php";
include "footer.php";
?>