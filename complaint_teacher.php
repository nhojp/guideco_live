<?php
session_start();
ob_start();

// Check if there's a toast message in the session
if (isset($_SESSION['toast_message']) && isset($_SESSION['toast_class'])) {
    $toast_message = $_SESSION['toast_message'];
    $toast_class = $_SESSION['toast_class'];

    // Unset the session variables so the message doesn't appear again
    unset($_SESSION['toast_message']);
    unset($_SESSION['toast_class']);
}

// Check if user is logged in and is an admin
if (!isset($_SESSION['loggedin']) || !isset($_SESSION['admin'])) {
    header('Location: index.php'); // Redirect if not logged in or not admin
    exit;
}

include "head.php";
include "sidebar.php";
include "conn.php";

// Fetch all complaints with status "pending"
$sql_pending = "SELECT * FROM complaints WHERE status = 'pending'";
$result_pending = $conn->query($sql_pending);

// Fetch all complaints with status "completed"
$sql_completed = "SELECT * FROM complaints WHERE status = 'complete'";
$result_completed = $conn->query($sql_completed);
?>
<div id="main">

    <?php include "header.php"; ?>
    <div class="container-fluid">
        <div class="container-fluid bg-white mt-2 rounded-lg border mb-2">
            <div class="row pt-3">
                <div class="col-md-6">
                    <div class="container-fluid p-2">
                        <h3><strong>Complaints List</strong></h3>
                    </div>
                </div>
                <div class="col-md-6">
                    <input class="form-control" type="text" id="searchInput" placeholder="Search school personnel">
                </div>
            </div>
            <div class="row pt-3 pb-3">
                <div class="col-md-12 text-right">
                    <div class="search-add-container">
                        <button type="button" class="btn btn-success add-btn" data-bs-toggle="dropdown" aria-expanded="false">
                            Add Complaints
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="admin-p-1.php">Complain a School Personnel</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <ul class="nav nav-tabs" id="complaintTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link active" id="pendingTab" data-bs-toggle="tab" href="#pending" role="tab" aria-controls="pending" aria-selected="true">Pending</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="completedTab" data-bs-toggle="tab" href="#completed" role="tab" aria-controls="completed" aria-selected="false">Completed</a>
            </li>
        </ul>

        <div class="tab-content mt-3" id="complaintTabsContent">
            <!-- Pending Tab -->
            <div class="tab-pane fade show active" id="pending" role="tabpanel" aria-labelledby="pendingTab">
                <div class="table-wrapper" style="position: relative; max-height: 400px; overflow-y: auto;">
                    <table class="table table-hover table-bordered mt-2 text-center">
                        <thead style="position: sticky; top: 0; background: #f8f9fa; z-index: 1;">
                            <tr>
                                <th style="width: 40%;">Complained Name</th>
                                <th style="width: 30%;">Position</th>
                                <th style="width:15%;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            <?php while ($row = $result_pending->fetch_assoc()) { ?>
                                <tr>
                                    <td><?php echo ucwords($row['complainedFirstName']) . ' ' . ucwords($row['complainedLastName']); ?></td>
                                    <td><?php echo ucwords($row['complainedDesignation']); ?></td>
                                    <td>
                                        <a href="complaint_teacher_1.php?id=<?php echo $row['id']; ?>" class="btn btn-outline-success"><i class="fas fa-eye"></i></a>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

            </div>

            <!-- Completed Tab -->
            <div class="tab-pane fade" id="completed" role="tabpanel" aria-labelledby="completedTab">
                <div class="table-wrapper" style="position: relative; max-height: 400px; overflow-y: auto;">
                    <table class="table table-hover table-bordered mt-2 text-center">
                        <thead>
                            <tr>
                                <th style="width: 40%;">Complained Name</th>
                                <th style="width: 30%;">Position</th>
                                <th style="width:15%;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            <?php while ($row = $result_completed->fetch_assoc()) { ?>
                                <tr>
                                    <td><?php echo ucwords($row['complainedFirstName']) . ' ' . ucwords($row['complainedLastName']); ?></td>
                                    <td><?php echo ucwords($row['complainedDesignation']); ?></td>
                                    <td>
                                        <button class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#viewModal<?php echo $row['id']; ?>"><i class="fas fa-eye"></i></button>
                                    </td>
                                </tr>

                                <!-- View Modal for Completed -->
                                <!-- View Modal -->
                                <div class="modal fade" id="viewModal<?php echo $row['id']; ?>" tabindex="-1" aria-labelledby="viewModalLabel<?php echo $row['id']; ?>" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header bg-success text-white">
                                                <h5 class="modal-title" id="viewModalLabel<?php echo $row['id']; ?>">Complaint Details</h5>
                                                <button type="button" class="btn-danger btn btn btn-circle" data-bs-dismiss="modal" aria-label="Close">x</button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="container">
                                                    <hr>
                                                    <div class="row section-header">
                                                        <div class="col text-center">
                                                            <h5><b>Victim Information</b></h5>
                                                        </div>
                                                    </div>
                                                    <hr>
                                                    <div class="row pt-2">
                                                        <div class="col-md-6">
                                                            <p><strong>Victim Name:</strong> <?php echo ucwords($row['victimFirstName']) . ' ' . ucwords($row['victimMiddleName']) . ' ' . ucwords($row['victimLastName']); ?></p>
                                                            <p><strong>Date of Birth:</strong> <?php echo $row['victimDOB']; ?></p>
                                                            <p><strong>Age:</strong> <?php echo $row['victimAge']; ?></p>
                                                            <p><strong>Sex:</strong> <?php echo ucwords($row['victimSex']); ?></p>
                                                            <p><strong>Grade:</strong> <?php echo ucwords($row['victimGrade']); ?></p>
                                                            <p><strong>Section:</strong> <?php echo ucwords($row['victimSection']); ?></p>
                                                            <p><strong>Adviser:</strong> <?php echo ucwords($row['victimAdviser']); ?></p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <p><strong>Mother Name:</strong> <?php echo ucwords($row['motherName']); ?></p>
                                                            <p><strong>Occupation:</strong> <?php echo ucwords($row['motherOccupation']); ?></p>
                                                            <p><strong>Contact:</strong> <?php echo $row['motherContact']; ?></p>
                                                            <p><strong>Address:</strong> <?php echo ucwords($row['motherAddress']); ?></p>
                                                            <p><strong>Father Name:</strong> <?php echo ucwords($row['fatherName']); ?></p>
                                                            <p><strong>Occupation:</strong> <?php echo ucwords($row['fatherOccupation']); ?></p>
                                                            <p><strong>Contact:</strong> <?php echo $row['fatherContact']; ?></p>
                                                            <p><strong>Address:</strong> <?php echo ucwords($row['fatherAddress']); ?></p>
                                                        </div>
                                                    </div>
                                                    <hr>
                                                    <div class="row section-header">
                                                        <div class="col text-center">
                                                            <h5><b>Complainant Information</b></h5>
                                                        </div>
                                                    </div>
                                                    <hr>
                                                    <div class="row pt-2">
                                                        <div class="col-md-6">
                                                            <p><strong>Name:</strong> <?php echo ucwords($row['complainantFirstName']) . ' ' . ucwords($row['complainantMiddleName']) . ' ' . ucwords($row['complainantLastName']); ?></p>
                                                            <p><strong>Relationship to Victim:</strong> <?php echo ucwords($row['relationshipToVictim']); ?></p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <p><strong>Contact:</strong> <?php echo $row['complainantContact']; ?></p>
                                                            <p><strong>Address:</strong> <?php echo ucwords($row['complainantAddress']); ?></p>
                                                        </div>
                                                    </div>
                                                    <hr>
                                                    <!-- Complained Person Information -->
                                                    <div class="row section-header">
                                                        <div class="col text-center">
                                                            <h5><b>Complained Person Information</b></h5>
                                                        </div>
                                                    </div>
                                                    <hr>
                                                    <div class="row pt-2">
                                                        <div class="col-md-6">
                                                            <p><strong>Name:</strong> <?php echo ucwords($row['complainedFirstName']) . ' ' . ucwords($row['complainedMiddleName']) . ' ' . ucwords($row['complainedLastName']); ?></p>
                                                            <p><strong>Position:</strong> <?php echo ucwords($row['complainedDesignation']); ?></p>
                                                        </div>
                                                    </div>
                                                    <hr>
                                                    <!-- Case Details -->
                                                    <div class="row section-header">
                                                        <div class="col text-center">
                                                            <h5><b>Case Details</b></h5>
                                                        </div>
                                                    </div>
                                                    <hr>
                                                    <div class="row pt-2">
                                                        <div class="col-md-12">
                                                            <p><strong>Details:</strong> <?php echo $row['caseDetails']; ?></p>
                                                        </div>
                                                    </div>
                                                    <hr>
                                                    <!-- Action Taken -->
                                                    <div class="row section-header">
                                                        <div class="col text-center">
                                                            <h5><b>Action Taken</b></h5>
                                                        </div>
                                                    </div>
                                                    <hr>
                                                    <div class="row pt-2">
                                                        <div class="col-md-12">
                                                            <p><strong>Action:</strong> <?php echo $row['actionTaken']; ?></p>
                                                        </div>
                                                    </div>
                                                    <hr>
                                                    <!-- Recommendation -->
                                                    <div class="row section-header">
                                                        <div class="col text-center">
                                                            <h5><b>Recommendation</b></h5>
                                                        </div>
                                                    </div>
                                                    <hr>
                                                    <div class="row pt-2">
                                                        <div class="col-md-12">
                                                            <p><strong>Recommendation:</strong> <?php echo $row['recommendations']; ?></p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <a href="complaint_print.php?id=<?php echo $row['id']; ?>" class="btn btn-success" target="_blank" style="width: 100%;"><i class="fas fa-print"></i> Print</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
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