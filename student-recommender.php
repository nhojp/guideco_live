<?php
session_start();
ob_start();

include 'head.php';
include 'student-nav.php';
include 'conn.php';

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || !isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$student_id = $_SESSION['user_id'];
date_default_timezone_set('Asia/Manila'); // Set the correct timezone for the Philippines

// Initialize variables for cooldown
$is_disabled = false;
$time_remaining = 0;

// Check the last entry time for the student
$sql = "SELECT created_at FROM feelings WHERE student_id = ? ORDER BY created_at DESC LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $last_submission_time = strtotime($row['created_at']);
    $current_time = time();
    $time_difference = $current_time - $last_submission_time;

    // 6 hours cooldown
    if ($time_difference < 6 * 3600) {
        $is_disabled = true;
        $time_remaining = (6 * 3600) - $time_difference;
    }
}
// Handling form submission to save the data in the database
$form_error = "";
$api_response = null;

// Fetch the last record of the student's feelings submission
$sql = "SELECT created_at FROM feelings WHERE student_id = ? ORDER BY created_at DESC LIMIT 1";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $last_submission = $result->fetch_assoc()['created_at'];
        $last_submission_time = strtotime($last_submission); // Convert to timestamp

        // Get the current time and calculate the difference
        $current_time = time();
        $time_diff = $current_time - $last_submission_time;

        // If the difference is less than 6 hours (21600 seconds), disable the button
        $is_disabled = ($time_diff < 21600);
    } else {
        $is_disabled = false; // No previous record, allow the button
    }

    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if the required fields are set
    if (isset($_POST['physical_feeling'], $_POST['emotional_feeling'], $_POST['mental_feeling'])) {
        $physical_feeling = $_POST['physical_feeling'];
        $emotional_feeling = $_POST['emotional_feeling'];
        $mental_feeling = $_POST['mental_feeling'];

        // Save data into database
        $sql = "INSERT INTO feelings (student_id, physical_feeling, emotional_feeling, mental_feeling, created_at)
                VALUES (?, ?, ?, ?, NOW())";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssss", $student_id, $physical_feeling, $emotional_feeling, $mental_feeling);
            if ($stmt->execute()) {
                // Data saved, now call the API
                $api_url = 'https://guideco.pythonanywhere.com/predict';
                $post_data = json_encode([
                    'physical_feeling' => $physical_feeling,
                    'emotional_feeling' => $emotional_feeling,
                    'mental_feeling' => $mental_feeling
                ]);

                $options = [
                    'http' => [
                        'method' => 'POST',
                        'header' => "Content-type: application/json\r\n",
                        'content' => $post_data
                    ]
                ];

                $context = stream_context_create($options);
                $response = file_get_contents($api_url, false, $context);

                if ($response === FALSE) {
                    $form_error = "Error calling the recommendation API.";
                } else {
                    $api_response = json_decode($response, true);
                }
            } else {
                $form_error = "Error saving data to the database: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $form_error = "Error preparing SQL statement.";
        }
    } else {
        $form_error = "Please fill out all the fields.";
    }
}
?>

<link rel="stylesheet" href="style-recommender.css">
<main class="flex-fill mt-5">
    <div class="container mt-5">
        <div class="row">
            <h1 class="text-center fw-bold mb-5">HOW DO YOU FEEL TODAY?</h1>
        </div>
        <form id="feelingForm" class="text-center" method="POST" action="">
            <!-- Physical Section -->
            <h3 class="mb-3">Physically</h3>
            <div class="row g-3">
                <div class="col-4 col-md-4">
                    <div class="form-check">
                        <input type="radio" id="sick" name="physical_feeling" value="sick" required>
                        <label for="sick">
                            <img src="img/sick.png" alt="Sick">
                            <span class="">Sick</span>
                        </label>
                    </div>
                </div>
                <div class="col-4 col-md-4">
                    <div class="form-check">
                        <input type="radio" id="tired" name="physical_feeling" value="tired" required>
                        <label for="tired">
                            <img src="img/tired.png" alt="Tired">
                            <span class="">Tired</span>
                        </label>
                    </div>
                </div>
                <div class="col-4 col-md-4">
                    <div class="form-check">
                        <input type="radio" id="energetic" name="physical_feeling" value="energetic" required>
                        <label for="energetic">
                            <img src="img/laughing.png" alt="Energetic">
                            <span class="">Energetic</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Emotional Section -->
            <h3 class="mb-3">Emotionally</h3>
            <div class="row g-3">
                <div class="col-4 col-md-4">
                    <div class="form-check">
                        <input type="radio" id="happy" name="emotional_feeling" value="happy" required>
                        <label for="happy">
                            <img src="img/happy.png" alt="Happy">
                            <span class="">Happy</span>
                        </label>
                    </div>
                </div>
                <div class="col-4 col-md-4">
                    <div class="form-check">
                        <input type="radio" id="sad" name="emotional_feeling" value="sad" required>
                        <label for="sad">
                            <img src="img/sad.png" alt="Sad">
                            <span class="">Sad</span>
                        </label>
                    </div>
                </div>
                <div class="col-4 col-md-4">
                    <div class="form-check">
                        <input type="radio" id="angry" name="emotional_feeling" value="angry" required>
                        <label for="angry">
                            <img src="img/angry.png" alt="Angry">
                            <span class="">Angry</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Mental Section -->
            <h3 class="mb-3">Mentally</h3>
            <div class="row g-3">
                <div class="col-4 col-md-4">
                    <div class="form-check">
                        <input type="radio" id="calm" name="mental_feeling" value="calm" required>
                        <label for="calm">
                            <img src="img/calm.png" alt="Calm">
                            <span class="">Calm</span>
                        </label>
                    </div>
                </div>
                <div class="col-4 col-md-4">
                    <div class="form-check">
                        <input type="radio" id="stressed" name="mental_feeling" value="stressed" required>
                        <label for="stressed">
                            <img src="img/hypnotized.png" alt="Stressed">
                            <span class="">Stressed</span>
                        </label>
                    </div>
                </div>
                <div class="col-4 col-md-4">
                    <div class="form-check">
                        <input type="radio" id="anxious" name="mental_feeling" value="anxious" required>
                        <label for="anxious">
                            <img src="img/anxious.png" alt="Anxious">
                            <span class="">Anxious</span>
                        </label>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-success mt-2 mb-5" <?php echo $is_disabled ? 'disabled' : ''; ?>>
                <?php
                if ($is_disabled) {
                    $hours = floor($time_remaining / 3600);
                    $minutes = floor(($time_remaining % 3600) / 60);
                    echo "Cooldown: $hours hrs $minutes mins";
                } else {
                    echo "Get Recommendation";
                }
                ?>
            </button>
            <div class="row">
            <a href="student-feeling.php" class="btn btn-outline-warning float-right">View History</a>

            </div>

        </form>

        <!-- Error Handling -->
        <?php if ($form_error): ?>
            <div class="alert alert-danger mt-3"><?= htmlspecialchars($form_error); ?></div>
        <?php endif; ?>
    </div>
</main>


<!-- Modal for Result -->
<div class="modal fade" id="resultModal" tabindex="-1" aria-labelledby="resultModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-center text-white">
                <h5 class="modal-title fw-bold" id="resultModalLabel">GuideCo recommends you to</h5>
                <a href="student-recommender.php" class="btn-close"></a>
            </div>
            <div class="modal-body text-center">
                <img src="img/cheerup.png" alt="Cheer Up" style="width: 200px; height: auto; margin-bottom: 20px;">
                <div class="container fw-bold" id="resultContent" style="font-size: 24px;">
                    <?php
                    if ($api_response && isset($api_response['recommendation'])) {
                        echo $api_response['recommendation'];
                    } else {
                        echo "No recommendation available.";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>

<script>
    // Wait until the document is ready
    document.addEventListener('DOMContentLoaded', function() {
        // Check if the API response exists to show the modal only after form submission
        const apiResponse = <?php echo json_encode($api_response); ?>;

        if (apiResponse && apiResponse.recommendation) {
            const resultModal = new bootstrap.Modal(document.getElementById('resultModal'));
            resultModal.show();
        }
    });
</script>


<?php include 'footer.php' ?>