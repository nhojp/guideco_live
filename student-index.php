<?php

include "conn.php";
include "head.php";

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['loggedin']) || !isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Initialize variables
$first_name = "Student";
$last_name = "";
$grade = "Unknown";
$section = "Unknown";
$violation_count = 0; // Initialize violation count

$user_id = $_SESSION['user_id'] ?? null;

if ($user_id) {
    // Fetch student ID and data based on user_id
    $sql = "SELECT 
        s.id AS student_id, 
        s.first_name, 
        s.last_name, 
        s.lrn,
        sec.section_name, 
        sec.grade_level
    FROM students s
    JOIN sections sec ON s.section_id = sec.id
    WHERE s.user_id = ?"; // Match the user_id

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows == 1) {
            // Fetch student details
            $row = $result->fetch_assoc();
            $first_name = $row['first_name'];
            $last_name = $row['last_name'];
            $lrn = $row['lrn'];
            $grade = $row['grade_level']; // Ensure this field exists
            $section = $row['section_name'];
            $student_id = $row['student_id']; // Get the student ID
        } else {
            // Handle case where no student was found
            echo "No student found with this user ID.";
        }

        $stmt->close();
    } else {
        // Handle query preparation error
        echo "Error preparing the student data query.";
    }

    // Fetch violation count for the student using student_id
    $sql_violations = "SELECT COUNT(*) AS violation_count 
                       FROM violations 
                       WHERE student_id = ?"; // Use student_id here

    if ($stmt = $conn->prepare($sql_violations)) {
        $stmt->bind_param("i", $student_id); // Ensure this matches the right student ID
        $stmt->execute();
        $result_violations = $stmt->get_result();

        if ($result_violations && $result_violations->num_rows == 1) {
            $row_violations = $result_violations->fetch_assoc();
            $violation_count = $row_violations['violation_count'];
        }

        $stmt->close();
    } else {
        // Handle query preparation error
        echo "Error preparing the violation count query.";
    }
} else {
    // Handle case where user ID is not set in session
    echo "User ID not set in session.";
}

include 'student-nav.php';
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@100;300;400;700;900&family=Old+English+Text+MT:wght@700&family=Times+New+Roman:wght@700&display=swap');

    .container-banner {
        position: relative;
        width: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
        margin-top: 40px;
        background-image: url('img/neshsbg.jpg');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        border-radius: 8px;
        padding: 20px;
        overflow: hidden;
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        text-align: center;
        font-family: 'Montserrat', sans-serif;
    }

    .container-banner::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: white;
        opacity: 0.7;
        z-index: 1;
    }

    .container-banner h1 {
        position: relative;
        z-index: 2;
        margin: 0;
        font-weight: 900;
        font-size: 2.5rem;
        color: #1D5B1B;
    }

    .container-banner h2 {
        position: relative;
        z-index: 2;
        margin: 0;
        font-weight: 400;
        font-size: 1.5rem;
        margin-top: 10px;
        color: black;
    }

    .container-vision {
        position: relative;
        width: 100%;
        padding: 20px;
        background-image: url('img/00.jpg');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        margin-top: 30px;
        background-color: rgba(30, 30, 30, 0.9);
        background-blend-mode: overlay;
    }

    .vision-title {
        font-family: 'Montserrat', sans-serif;
        font-weight: bold;
        font-size: 60px;
        color: white;
        margin: 30px;
    }

    .vision-body {
        font-family: 'Montserrat', sans-serif;
        font-size: 18px;
        color: white;
        line-height: 1.6;
        margin-top: 10px;
        max-width: 900px;
        margin: 30px;
    }

    .container-mission {
        position: relative;
        width: 100%;
        padding: 20px;
        background-image: url('img/01.jpg');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        margin-top: 30px;
        background-color: rgba(30, 30, 30, 0.9);
        background-blend-mode: overlay;
    }

    .mission-title {
        font-family: 'Montserrat', sans-serif;
        font-weight: bold;
        font-size: 60px;
        color: white;
        margin: 30px;
    }

    .mission-body {
        font-family: 'Montserrat', sans-serif;
        font-size: 18px;
        color: white;
        line-height: 1.6;
        margin-top: 10px;
        max-width: 900px;
        margin: 30px;
    }

    .container-values {
        position: relative;
        width: 100%;
        padding: 20px;
        background-image: url('img/02.jpg');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        margin-top: 30px;
        background-color: rgba(30, 30, 30, 0.9);
        background-blend-mode: overlay;
    }

    .values-title {
        font-family: 'Montserrat', sans-serif;
        font-weight: bold;
        font-size: 60px;
        color: white;
        margin: 30px;
    }

    .values-body {
        font-family: 'Montserrat', sans-serif;
        font-size: 18px;
        color: white;
        line-height: 1.6;
        margin-top: 10px;
        max-width: 900px;
        margin: 30px;
    }
</style>

<main class="flex-fill mt-5">
    <div class="container mt-5">
        <div class="container-fluid bg-success text-white mt-2 rounded-lg pb-2 border">
            <div class="container-fluid p-4">
                <div class="row">
                    <div class="col-md-12" id="animate-area">
                        <h2 class="font-weight-bold">
                            <?php echo ucwords($first_name . ' ' . $last_name); ?>
                        </h2>

                        <p>
                            <?php echo ucwords($grade . ' - ' . $section); ?>
                        </p>
                        <a href="student-violation.php?student_id=<?php echo $student_id; ?>">
                            <p>
                                <?php
                                // Display violation count with badge color
                                if ($violation_count == 0) {
                                    echo '<span class="badge badge-success">No Violations</span>';
                                } elseif ($violation_count == 1 || $violation_count == 2) {
                                    echo '<span class="badge badge-warning">' . $violation_count . ' Violations</span>';
                                } elseif ($violation_count >= 3) {
                                    echo '<span class="badge badge-danger">' . $violation_count . ' Violations</span>';
                                }
                                ?>
                            </p>
                        </a>
                    </div>
                </div>
                <div class="row">
                    <a href="student-counseling.php"><button class="btn btn-outline-light float-right">Ask for Counseling</button></a>
                </div>
            </div>
        </div>

        <div class="container-banner">
            <h1 class="banner-title">Welcome to GuideCo!</h1>
            <h2 class="banner-subtitle">The Guidance and Counseling System of Nasugbu East Senior High School</h2>
        </div>

        <div class="container-vision">
            <h2 class="vision-title">Our Vision</h2>
            <p class="vision-body">To be a center of excellence in education, cultivating globally competitive and socially responsible individuals who contribute to nation-building through academic and holistic development.</p>
        </div>

        <div class="container-mission">
            <h2 class="mission-title">Our Mission</h2>
            <p class="mission-body">To provide quality education that nurtures academic success, character development, and leadership skills, preparing students to excel and contribute to society</p>
        </div>

        <div class="container-values">
            <h2 class="values-title">Our values</h2>
            <p class="values-body">To provide quality education that nurtures academic success, character development, and leadership skills, preparing students to excel and contribute to society</p>
        </div>





        <div class="container mt-4">
            <!-- ChatLing Widget Integration -->
            <div id="chatling-embed-container"></div>
            <script async data-id="7485494224" id="chatling-embed-script" type="text/javascript" src="https://chatling.ai/js/embed.js"></script>
        </div>
    </div>
</main>

<?php include 'footer.php'; ?>