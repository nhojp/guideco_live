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

    $sql = "SELECT * FROM complaints WHERE id = ?";
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
    $sql = "UPDATE complaints 
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
                complainedDesignation = ?,
                complainedAddress = ?, 
                complainedContact = ?, 
                
                caseDetails = ?, 
                actionTaken = ?, 
                recommendations = ?
            WHERE id = ?";

    if ($stmt = $conn->prepare($sql)) {
        // Bind parameters to the statement
        $stmt->bind_param(
            "ssssissssssssssssssssssssssisssssssi",
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
            $_POST['complainedDesignation'],
            $_POST['complainedAddress'],
            $_POST['complainedContact'],

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
    $sql = "UPDATE complaints 
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
                complainedDesignation = ?,
                complainedAddress = ?, 
                complainedContact = ?, 

                caseDetails = ?, 
                actionTaken = ?, 
                recommendations = ?
            WHERE id = ?";

    if ($stmt = $conn->prepare($sql)) {
        // Bind parameters to the statement
        $stmt->bind_param(
            "ssssissssssssssssssssssssssisssssssi",
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
            $_POST['complainedDesignation'],
            $_POST['complainedAddress'],
            $_POST['complainedContact'],
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
    $sql = "DELETE FROM complaints WHERE id = ?";
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

$conn->close();
?>

<div id="main">

    <?php include "header.php"; ?>
    <div class="container-fluid mb-2">
        <div class="container-fluid bg-white mt-2 rounded-lg border">
            <div class="row pt-3">
                <div class="col-md-6">
                    <h3 class="p-2"><strong>COMPLAINT DETAILS</strong></h3>
                </div>
            </div>
        </div>
        <form action="" method="post">
            <div class="container-fluid bg-white mt-2 rounded-lg border">
                <h5 class="text-center mt-3 text-success"><strong>Victim Details</strong></h5>
                <div class="form-row mt-3">
                    <div class="col-md-4">
                        <strong>First Name:</strong>
                        <input type="text" class="form-control" name="victimFirstName"
                            value="<?php echo htmlspecialchars($complaint_data['victimFirstName'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <strong>Middle Initial:</strong>
                        <input type="text" class="form-control" name="victimMiddleName"
                            value="<?php echo htmlspecialchars($complaint_data['victimMiddleName'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <strong>Last Name:</strong>
                        <input type="text" class="form-control" name="victimLastName"
                            value="<?php echo htmlspecialchars($complaint_data['victimLastName'] ?? ''); ?>" required>
                    </div>
                </div>
                <div class="form-row mt-3">
                    <div class="col-md-6">
                        <strong>Date of Birth:</strong>
                        <input type="date" class="form-control" name="victimDOB"
                            value="<?php echo htmlspecialchars($complaint_data['victimDOB'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <strong>Sex:</strong>
                        <select class="form-control" name="victimSex" required>
                            <option value="Male" <?php echo (isset($complaint_data['victimSex']) && $complaint_data['victimSex'] === 'Male') ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo (isset($complaint_data['victimSex']) && $complaint_data['victimSex'] === 'Female') ? 'selected' : ''; ?>>Female</option>
                        </select>
                    </div>

                </div>
                <div class="form-row mt-3 pb-4">
                    <div class="col-md-3"><strong>Grade:</strong>
                        <input type="text" class="form-control" name="victimGrade"
                            value="<?php echo htmlspecialchars($complaint_data['victimGrade'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-3"><strong>Section:</strong>
                        <input type="text" class="form-control" name="victimSection"
                            value="<?php echo htmlspecialchars($complaint_data['victimSection'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-6"><strong>Adviser:</strong>
                        <input type="text" class="form-control" name="victimAdviser"
                            value="<?php echo htmlspecialchars($complaint_data['victimAdviser'] ?? ''); ?>" required>
                    </div>

                </div>
                <hr>
                <div class="form-row mt-3 pb-3">
                    <div class="col-md-6">
                        <div class="container-fluid">
                            <h5 class="text-center mt-3 text-success"><strong>Mother</strong></h5>
                            <div class="row">
                                <strong>Name:</strong>
                                <input type="text" class="form-control" name="motherName"
                                    value="<?php echo ucwords(htmlspecialchars($complaint_data['motherName'] ?? '')); ?>" required>
                            </div>
                            <div class="row mt-3">
                                <strong>Occupation:</strong>
                                <input type="text" class="form-control" name="motherOccupation"
                                    value="<?php echo ucwords(htmlspecialchars($complaint_data['motherOccupation'] ?? '')); ?>" required>
                            </div>
                            <div class="row mt-3">
                                <strong>Address:</strong>
                                <input type="text" class="form-control" name="motherAddress"
                                    value="<?php echo ucwords(htmlspecialchars($complaint_data['motherAddress'] ?? '')); ?>" required>
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
                                    value="<?php echo ucwords(htmlspecialchars($complaint_data['fatherName'] ?? '')); ?>" required>
                            </div>
                            <div class="row mt-3">
                                <strong>Occupation:</strong>
                                <input type="text" class="form-control" name="fatherOccupation"
                                    value="<?php echo ucwords(htmlspecialchars($complaint_data['fatherOccupation'] ?? '')); ?>" required>
                            </div>
                            <div class="row mt-3">
                                <strong>Address:</strong>
                                <input type="text" class="form-control" name="fatherAddress"
                                    value="<?php echo ucwords(htmlspecialchars($complaint_data['fatherAddress'] ?? '')); ?>" required>
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
                            value="<?php echo htmlspecialchars($complaint_data['complainedFirstName'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <strong>Middle Name:</strong>
                        <input type="text" class="form-control" name="complainedMiddleName"
                            value="<?php echo htmlspecialchars($complaint_data['complainedMiddleName'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <strong>Last Name:</strong>
                        <input type="text" class="form-control" name="complainedLastName"
                            value="<?php echo htmlspecialchars($complaint_data['complainedLastName'] ?? ''); ?>" required>
                    </div>
                </div>
                <div class="form-row mt-3">
                    <div class="col-md-8">
                        <strong>Date of Birth</strong>
                        <input type="date" class="form-control" name="complainedDOB"
                            value="<?php echo htmlspecialchars($complaint_data['complainedDOB'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <strong>Sex:</strong>
                        <select class="form-control" name="complainedSex" required>
                            <option value="Male" <?php echo (isset($complaint_data['complainedSex']) && $complaint_data['complainedSex'] === 'Male') ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo (isset($complaint_data['complainedSex']) && $complaint_data['complainedSex'] === 'Female') ? 'selected' : ''; ?>>Female</option>
                        </select>
                    </div>

                </div>
                <div class="form-row mt-3">
                    <div class="col-md-12">
                        <strong>Designation/Position</strong>
                        <input type="text" class="form-control" name="complainedDesignation"
                            value="<?php echo htmlspecialchars($complaint_data['complainedDesignation'] ?? ''); ?>" required>
                    </div>
                </div>

                <div class="form-row mt-3 mb-3">
                    <div class="col-md-6">
                        <strong>Address:</strong>
                        <input type="text" class="form-control" name="complainedAddress"
                            value="<?php echo htmlspecialchars($complaint_data['complainedAddress'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <strong>Contact Number:</strong>
                        <input type="text" class="form-control" name="complainedContact"
                            value="<?php echo htmlspecialchars($complaint_data['complainedContact'] ?? ''); ?>" required
                            pattern="09\d{9}"
                            maxlength="11"
                            title="Please enter a valid 11-digit number starting with 09."
                            value="09"
                            oninput="if (!this.value.startsWith('09')) this.value = '09' + this.value.replace(/^09*/, ''); this.value = this.value.replace(/\D/g, '');">
                    </div>
                </div>
                <hr>
                <h5 class="text-center mt-3 text-success"><strong>Complainant Details</strong></h5>
                <div class="form-row mt-3">
                    <div class="col-md-4">
                        <strong>First Name:</strong>
                        <input type="text" class="form-control" name="complainantFirstName"
                            value="<?php echo htmlspecialchars($complaint_data['complainantFirstName'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <strong>Middle Name:</strong>
                        <input type="text" class="form-control" name="complainantMiddleName"
                            value="<?php echo htmlspecialchars($complaint_data['complainantMiddleName'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <strong>Last Name:</strong>
                        <input type="text" class="form-control" name="complainantLastName"
                            value="<?php echo htmlspecialchars($complaint_data['complainantLastName'] ?? ''); ?>" required>
                    </div>
                </div>
                <div class="form-row mt-3">
                    <div class="col-md-12">
                        <strong>Relationship to Victim</strong>
                        <input type="text" class="form-control" name="relationshipToVictim"
                            value="<?php echo htmlspecialchars($complaint_data['relationshipToVictim'] ?? ''); ?>" required>
                    </div>
                </div>
                <div class="form-row mt-3 mb-3">
                    <div class="col-md-6">
                        <strong>Contact Number:</strong>
                        <input type="text" class="form-control" name="complainantContact"
                            value="<?php echo htmlspecialchars($complaint_data['complainantContact'] ?? ''); ?>" required
                            pattern="09\d{9}"
                            maxlength="11"
                            title="Please enter a valid 11-digit number starting with 09."
                            value="09"
                            oninput="if (!this.value.startsWith('09')) this.value = '09' + this.value.replace(/^09*/, ''); this.value = this.value.replace(/\D/g, '');">
                    </div>
                    <div class="col-md-6">
                        <strong>Address:</strong>
                        <input type="text" class="form-control" name="complainantAddress"
                            value="<?php echo htmlspecialchars($complaint_data['complainantAddress'] ?? ''); ?>" required>
                    </div>
                </div>

                <hr>
                <h5 class="text-center mt-3 text-success mb-2"><strong>Case Details</strong></h5>
                <!-- Case Details Section -->
                <div class="form-group">
                    <label for="caseDetails"><strong>Case Details:</strong></label>
                    <textarea class="form-control" id="caseDetails" name="caseDetails" rows="5" placeholder="Enter the details of the case..." required><?php echo htmlspecialchars($complaint_data['caseDetails'] ?? ''); ?></textarea>
                </div>

                <!-- Action Taken Section -->
                <div class="form-group">
                    <label for="actionTaken"><strong>Action Taken:</strong></label>
                    <textarea class="form-control" id="actionTaken" name="actionTaken" rows="5" placeholder="Enter the details of the action taken by the school..." required><?php echo htmlspecialchars($complaint_data['actionTaken'] ?? ''); ?></textarea>
                </div>

                <!-- Recommendations Section -->
                <div class="form-group">
                    <label for="recommendations"><strong>Recommendations:</strong></label>
                    <textarea class="form-control" id="recommendations" name="recommendations" rows="5" placeholder="Enter the recommendations of the school..." required><?php echo htmlspecialchars($complaint_data['recommendations'] ?? ''); ?></textarea>
                </div>

                <!-- Submit Button -->
                <div class="form-row mt-2 mb-2">
                    <div class="col-md-12 text-right">
                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#discardModal">
                            Discard
                        </button> <button type="submit" name="save" class="btn btn-outline-success">Save changes</button>
                        <button type="submit" class="btn btn-success" name="approve">Complete</button>
                    </div>
                </div>
            </div>
        </form>
        <!-- Modal -->
        <div class="modal fade" id="discardModal" tabindex="-1" aria-labelledby="discardModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="discardModalLabel">Confirm Discard</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Are you sure you want to discard this?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-success" data-bs-dismiss="modal">Cancel</button>
                        <form method="POST" action="" style="display:inline;">
                            <button type="submit" name="discard" class="btn btn-danger">Yes, Discard</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</div>

<?php
include "toast.php";
include "footer.php";
?>