<?php
session_start();
ob_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['loggedin']) || !isset($_SESSION['admin'])) {
    header('Location: index.php'); // Redirect if not logged in or not admin
    exit;
}

include "head.php";
include "sidebar.php";
include "conn.php";

// Get filters
$violationType = $_GET['violationType'] ?? '';
$grade = $_GET['grade'] ?? '';
$section = $_GET['section'] ?? '';
$timePeriod = $_GET['timePeriod'] ?? 'thisYear';
$sex = $_GET['sex'] ?? '';

// Date filter logic
$currentDate = date('Y-m-d');
if ($timePeriod == 'thisDay') {
    $startDate = $currentDate;
    $endDate = $currentDate . ' 23:59:59';
} elseif ($timePeriod == 'thisMonth') {
    $startDate = date('Y-m-01');
    $endDate = date('Y-m-t') . ' 23:59:59';
} else {
    $startDate = date('Y-01-01');
    $endDate = date('Y-12-31') . ' 23:59:59';
}

$schoolYear = $_GET['schoolYear'] ?? '';

$query = "SELECT DISTINCT v.id, v.student_id, v.reported_at, v.guard_id, v.teacher_id, v.violation_id, s.first_name, s.last_name, s.sex 
          FROM violations v
          JOIN students s ON v.student_id = s.id
          JOIN sections sec ON s.section_id = sec.id
          JOIN school_year sy ON YEAR(v.reported_at) BETWEEN sy.year_start AND sy.year_end 
          WHERE v.reported_at BETWEEN '$startDate' AND '$endDate'";
if ($schoolYear) {
    $query .= " AND sy.id = '$schoolYear'";
}
if ($violationType) {
    $query .= " AND v.violation_id = '$violationType'";
}
if ($grade) {
    $query .= " AND sec.grade_level = '$grade'";
}
if ($section) {
    $query .= " AND sec.id = '$section'";
}
if ($sex) {
    $query .= " AND s.sex = '$sex'";
}

$result = mysqli_query($conn, $query);
$violations = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Fetch filters data
$violationListResult = mysqli_query($conn, "SELECT * FROM violation_list");
$violationList = mysqli_fetch_all($violationListResult, MYSQLI_ASSOC);

$sectionsResult = mysqli_query($conn, "SELECT * FROM sections");
$sections = mysqli_fetch_all($sectionsResult, MYSQLI_ASSOC);

// Group data for charts
$totalViolations = count($violations);
$violationsByType = array_count_values(array_column($violations, 'violation_id'));
$violationsByGender = array_count_values(array_column($violations, 'sex'));
$studentsWithViolationsToday = array_filter($violations, function ($violation) use ($currentDate) {
    return strpos($violation['reported_at'], $currentDate) === 0;
});


// Fetch school years
$schoolYearsResult = mysqli_query($conn, "SELECT * FROM school_year");
$schoolYears = mysqli_fetch_all($schoolYearsResult, MYSQLI_ASSOC);

// Count violators by grade
$violatorsByGrade = [
    'Grade 11' => 0,
    'Grade 12' => 0,
];

// Query to count the occurrences of each specific feeling in the physical_feeling column (with today's date filter)
$physicalQuery = "SELECT physical_feeling, COUNT(*) AS count 
                  FROM feelings 
                  WHERE physical_feeling IS NOT NULL 
                  AND DATE(created_at) = '$currentDate' 
                  GROUP BY physical_feeling 
                  ORDER BY count DESC LIMIT 1";
$physicalResult = mysqli_query($conn, $physicalQuery);
$physicalFeeling = mysqli_fetch_assoc($physicalResult);

// Query to count the occurrences of each specific feeling in the emotional_feeling column (with today's date filter)
$emotionalQuery = "SELECT emotional_feeling, COUNT(*) AS count 
                   FROM feelings 
                   WHERE emotional_feeling IS NOT NULL 
                   AND DATE(created_at) = '$currentDate' 
                   GROUP BY emotional_feeling 
                   ORDER BY count DESC LIMIT 1";
$emotionalResult = mysqli_query($conn, $emotionalQuery);
$emotionalFeeling = mysqli_fetch_assoc($emotionalResult);

// Query to count the occurrences of each specific feeling in the mental_feeling column (with today's date filter)
$mentalQuery = "SELECT mental_feeling, COUNT(*) AS count 
                FROM feelings 
                WHERE mental_feeling IS NOT NULL 
                AND DATE(created_at) = '$currentDate' 
                GROUP BY mental_feeling 
                ORDER BY count DESC LIMIT 1";
$mentalResult = mysqli_query($conn, $mentalQuery);
$mentalFeeling = mysqli_fetch_assoc($mentalResult);
?>

<!-- Include Bootstrap from head.php -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var filterForm = document.getElementById('filterForm');
        filterForm.querySelectorAll('select').forEach(function(select) {
            select.addEventListener('change', function() {
                filterForm.submit();
            });
        });
    });
</script>


<div id="main">

    <?php include "header.php"; ?>
    <div class="container-fluid">
        <div class="container-fluid bg-white mt-2 mb-2 rounded-lg border">
            <div class="row pt-3">
                <div class="col-md-3">
                    <div class="container-fluid">
                        <h3><strong>Dashboard</strong></h3>
                    </div>
                </div>
                <form id="filterForm" class="col-md-9">
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group" style="width:100%">
                                <select class="form-control" id="violationType" name="violationType">
                                    <option value="">All Violation</option>
                                    <?php foreach ($violationList as $violation) : ?>
                                        <option value="<?= $violation['id'] ?>" <?= $violation['id'] == $violationType ? 'selected' : '' ?>>
                                            <?= ucwords($violation['violation_description']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group" style="width:100%">
                                <select class="form-control" id="grade" name="grade">
                                    <option value="">All Grade</option>
                                    <option value="11" <?= $grade == 11 ? 'selected' : '' ?>>Grade 11</option>
                                    <option value="12" <?= $grade == 12 ? 'selected' : '' ?>>Grade 12</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group" style="width:100%">
                                <select class="form-control" id="section" name="section">
                                    <option value="">All Section</option>
                                    <?php foreach ($sections as $sectionItem) : ?>
                                        <option value="<?= $sectionItem['id'] ?>" <?= $sectionItem['id'] == $section ? 'selected' : '' ?>>
                                            <?= ucwords($sectionItem['section_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group" style="width:100%">
                                <select class="form-control" id="timePeriod" name="timePeriod">
                                    <option value="thisYear" <?= $timePeriod == 'thisYear' ? 'selected' : '' ?>>This Year</option>
                                    <option value="thisMonth" <?= $timePeriod == 'thisMonth' ? 'selected' : '' ?>>This Month</option>
                                    <option value="thisDay" <?= $timePeriod == 'thisDay' ? 'selected' : '' ?>>This Day</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group" style="width:100%">
                                <select class="form-control" id="sex" name="sex">
                                    <option value="">All Sex</option>
                                    <option value="Male" <?= $sex == 'Male' ? 'selected' : '' ?>>Male</option>
                                    <option value="Female" <?= $sex == 'Female' ? 'selected' : '' ?>>Female</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group" style="width:100%">
                                <select class="form-control" id="schoolYear" name="schoolYear">
                                    <option value="">All School Year</option>
                                    <?php foreach ($schoolYears as $year) : ?>
                                        <option value="<?= $year['id'] ?>" <?= $year['id'] == ($_GET['schoolYear'] ?? '') ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($year['year_start']) . ' - ' . htmlspecialchars($year['year_end']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <hr>

            <div class="row p-2">
                <div class="col-md-2">
                    <div class="row">
                        <div class="card border-grey d-flex flex-column justify-content-center">
                            <div class="card-body text-black text-center">
                                <h4 class="card-title">Total Violations</h4>
                                <hr>
                                <h1 class="card-text text-success display-1 mt-2 mb-3" style="font-size: calc(1.5rem + 2vw);"><strong><?= $totalViolations ?></strong></h1>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-2 text-black text-center">
                        <h4>Violator by Sex</h4>
                        <div class="container p-2 rounded border">
                            <canvas id="violationsPieChart"></canvas>
                        </div>
                    </div>

                </div>
                <div class="col-md-8 ">
                    <h4>Types of Violation</h4>
                    <div class="container p-2 rounded border">
                        <canvas id="violationsBarChart"></canvas>
                    </div>
                </div>
                <div class="col-md-2 border rounded">
                    <h4 class="text-center pt-2">Student Feeling Trends</h4>
                    <div class="row">
                        <div class="col-12 mb-2">
                            <div class="card border-grey shadow-sm">
                                <div class="card-body text-black text-center">
                                    <h5 class="card-title">Physically</h5>
                                    <hr>
                                    <h5 class="card-text fw-bold text-success"><?= ucwords($physicalFeeling['physical_feeling']) ?> (<?= $physicalFeeling['count'] ?>)</h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 mb-2">
                            <div class="card border-grey shadow-sm">
                                <div class="card-body text-black text-center">
                                    <h5 class="card-title">Emotionally</h5>
                                    <hr>
                                    <h5 class="card-text fw-bold text-success"><?= ucwords($emotionalFeeling['emotional_feeling']) ?> (<?= $emotionalFeeling['count'] ?>)</h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="card border-grey shadow-sm">
                                <div class="card-body text-black text-center">
                                    <h5 class="card-title">Mentally</h5>
                                    <hr>
                                    <h5 class="card-text fw-bold text-success"><?= ucwords($mentalFeeling['mental_feeling']) ?> (<?= $mentalFeeling['count'] ?>)</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <style>
        #violationsBarChart,
        #violationsPieChart {
            height: 100%;
            max-height: 380px;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Bar Chart
            var ctxBar = document.getElementById('violationsBarChart').getContext('2d');
            var barChart = new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: <?= json_encode(array_map(fn($id) => ucwords($violationList[array_search($id, array_column($violationList, 'id'))]['violation_description']), array_keys($violationsByType))) ?>,
                    datasets: [{
                        label: 'Violations',
                        data: <?= json_encode(array_values($violationsByType)) ?>,
                        backgroundColor: ['#082D0F'],
                    }]
                },
                options: {
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    },
                    scales: {
                        x: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Violation Type'
                            },
                            ticks: {
                                autoSkip: false, // Ensures all labels are shown
                                maxRotation: 90, // Rotate labels if needed
                                minRotation: 45 // Rotate labels if needed
                            }
                        },
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Number of Violations'
                            },
                            ticks: {
                                stepSize: 1
                            }
                        }
                    },
                    backgroundColor: '#f5f5f5' // Custom Background Color for Chart Area
                }


            });

            // Pie Chart
            var ctxPie = document.getElementById('violationsPieChart').getContext('2d');
            var pieChart = new Chart(ctxPie, {
                type: 'pie',
                data: {
                    labels: ['Male', 'Female'],
                    datasets: [{
                        data: [
                            <?= $violationsByGender['Male'] ?? 0 ?>,
                            <?= $violationsByGender['Female'] ?? 0 ?>
                        ],
                        backgroundColor: ['#36A2EB', '#FF6384']
                    }]
                },
                options: {
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    }
                }
            });
        });
    </script>
</div>

<?php
include "toast.php";
include "footer.php";
?>