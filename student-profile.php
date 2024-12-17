<?php
session_start();

// Include necessary files
include('conn.php');
include('head.php');
include 'student-nav.php';

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || !isset($_SESSION['user_id'])) {
    // Redirect if not logged in
    header('Location: index.php');
    exit;
}

function calculateAge($birthdate)
{
    $birthDate = new DateTime($birthdate);
    $currentDate = new DateTime();
    $age = $currentDate->diff($birthDate)->y;
    return $age;
}




// Get the user_id from the session
$user_id = $_SESSION['user_id'];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_all']) && isset($_POST['student_id'])) {
        $student_id = $_POST['student_id'];
        $first_name = $_POST['first_name'];
        $middle_name = $_POST['middle_name'];
        $last_name = $_POST['last_name'];
        $birthdate = $_POST['birthdate'];
        $age = calculateAge($birthdate); // calculate age
        $sex = $_POST['sex'];
        $religion = $_POST['religion'];
        $contact_number = $_POST['contact_number'];

        // Mother's Info
        $mother_name = isset($_POST['mother_name']) ? $_POST['mother_name'] : 'N/A';
        $mother_contact_number = isset($_POST['mother_contact_number']) ? $_POST['mother_contact_number'] : 'N/A';
        $mother_email = isset($_POST['mother_email']) ? $_POST['mother_email'] : 'N/A';
        $mother_occupation = isset($_POST['mother_occupation']) ? $_POST['mother_occupation'] : 'N/A';
        $mother_address = isset($_POST['mother_address']) ? $_POST['mother_address'] : 'N/A';

        // Father's Info
        $father_name = isset($_POST['father_name']) ? $_POST['father_name'] : 'N/A';
        $father_contact_number = isset($_POST['father_contact_number']) ? $_POST['father_contact_number'] : 'N/A';
        $father_email = isset($_POST['father_email']) ? $_POST['father_email'] : 'N/A';
        $father_occupation = isset($_POST['father_occupation']) ? $_POST['father_occupation'] : 'N/A';
        $father_address = isset($_POST['father_address']) ? $_POST['father_address'] : 'N/A';

        // Account Info
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = $_POST['password'];

        // Start a transaction to ensure all updates happen together
        $conn->begin_transaction();

        try {
            // Update student personal info
            $sql_update_student = "UPDATE students SET first_name = ?, middle_name = ?, last_name = ?, birthdate = ?, age = ?, sex = ?, religion = ?, contact_number = ? WHERE id = ?";
            $stmt_update_student = $conn->prepare($sql_update_student);
            $stmt_update_student->bind_param("ssssisssi", $first_name, $middle_name, $last_name, $birthdate, $age, $sex, $religion, $contact_number, $student_id);
            if (!$stmt_update_student->execute()) {
                throw new Exception('Error updating student: ' . $stmt_update_student->error);
            }
            $stmt_update_student->close();

            // Update mother info
            $sql_update_mother = "UPDATE mothers SET name = ?, contact_number = ?, email = ?, occupation = ?, address = ? WHERE student_id = ?";
            $stmt_update_mother = $conn->prepare($sql_update_mother);
            $stmt_update_mother->bind_param("sssssi", $mother_name, $mother_contact_number, $mother_email, $mother_occupation, $mother_address, $student_id);
            if (!$stmt_update_mother->execute()) {
                throw new Exception('Error updating mother: ' . $stmt_update_mother->error);
            }
            $stmt_update_mother->close();

            // Update father info
            $sql_update_father = "UPDATE fathers SET name = ?, contact_number = ?, email = ?, occupation = ?, address = ? WHERE student_id = ?";
            $stmt_update_father = $conn->prepare($sql_update_father);
            $stmt_update_father->bind_param("sssssi", $father_name, $father_contact_number, $father_email, $father_occupation, $father_address, $student_id);
            if (!$stmt_update_father->execute()) {
                throw new Exception('Error updating father: ' . $stmt_update_father->error);
            }
            $stmt_update_father->close();

            // Update user account info
            $sql_update_account = "UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?";
            $stmt_update_account = $conn->prepare($sql_update_account);
            $stmt_update_account->bind_param("sssi", $username, $email, $password, $user_id);
            if (!$stmt_update_account->execute()) {
                throw new Exception('Error updating account: ' . $stmt_update_account->error);
            }
            $stmt_update_account->close();

            // Commit the transaction
            $conn->commit();
            echo "Profile updated successfully.";
        } catch (Exception $e) {
            // If any query fails, rollback the transaction
            $conn->rollback();
            die($e->getMessage());
        }
    }
}

$sql_students = "SELECT id, first_name, middle_name, last_name FROM students WHERE user_id = ?";
$stmt_students = $conn->prepare($sql_students);
$stmt_students->bind_param("i", $user_id);
$stmt_students->execute();
$result_students = $stmt_students->get_result();
$student = $result_students->fetch_assoc(); // Fetch one row


// Fetch the logged-in user's data from the users table
$sql_user = "SELECT * FROM users WHERE id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user_data = $result_user->fetch_assoc();

// Fetch all data from the students table who have the user_id
$sql_students = "SELECT id, first_name, middle_name, last_name, age, sex, contact_number, religion, birthdate FROM students WHERE user_id = ?";
$stmt_students = $conn->prepare($sql_students);
$stmt_students->bind_param("i", $user_id);
$stmt_students->execute();
$result_students = $stmt_students->get_result();
$students_data = $result_students->fetch_all(MYSQLI_ASSOC);

// Fetch all data from mothers and fathers table based on student_id
$students_ids = array_column($students_data, 'id');
$mothers_data = [];
$fathers_data = [];

if (!empty($students_ids)) {
    $placeholders = implode(',', array_fill(0, count($students_ids), '?'));

    // Fetch data from mothers table
    $sql_mothers = "SELECT parent_id, student_id, name, contact_number, email, occupation, address FROM mothers WHERE student_id IN ($placeholders)";
    $stmt_mothers = $conn->prepare($sql_mothers);
    $stmt_mothers->bind_param(str_repeat('i', count($students_ids)), ...$students_ids);
    $stmt_mothers->execute();
    $result_mothers = $stmt_mothers->get_result();
    $mothers_data = $result_mothers->fetch_all(MYSQLI_ASSOC);

    // Fetch data from fathers table
    $sql_fathers = "SELECT parent_id, student_id, name, contact_number, email, occupation, address FROM fathers WHERE student_id IN ($placeholders)";
    $stmt_fathers = $conn->prepare($sql_fathers);
    $stmt_fathers->bind_param(str_repeat('i', count($students_ids)), ...$students_ids);
    $stmt_fathers->execute();
    $result_fathers = $stmt_fathers->get_result();
    $fathers_data = $result_fathers->fetch_all(MYSQLI_ASSOC);
}


if (!empty($student['first_name']) || !empty($student['middle_name']) || !empty($student['last_name'])) {
    // Concatenate available names
    $full_name = trim(
        (!empty($student['first_name']) ? $student['first_name'] . ' ' : '') .
            (!empty($student['middle_name']) ? $student['middle_name'] . ' ' : '') .
            (!empty($student['last_name']) ? $student['last_name'] : '')
    );
} else {
    // Default to 'N/A' if all fields are empty
    $full_name = 'N/A';
}
?>


<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

<style>
    body {
        background-color: #f4f7fc;
        font-family: 'Montserrat', sans-serif;
        margin: 0;
        padding: 0;
    }

    .containerk {
        background-color: #fff;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        max-width: 1200px;
        margin: 30px auto;
    }

    h1 {
        color: white;
        font-size: 2.5rem;
        font-weight: 600;
        margin-bottom: 20px;
        margin-top: 10px;
    }

    h2 {
        color: #333;
        font-size: 1.5rem;
        font-weight: 500;
        margin-top: 20px;
        margin-bottom: 20px;
    }

    .profile-img {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    }

    label {
        font-weight: bold;
        font-weight: 200;
    }

    .form-control,
    .form-select {
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #1F5F1E;
        box-shadow: 0 0 0 0.25rem rgba(31, 95, 30, 0.25);
    }

    .btn-primary {
        background-color: #1F5F1E;
        border-color: #1F5F1E;
        border-radius: 8px;
        padding: 10px 20px;
        font-weight: bold;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        background-color: #155c14;
        border-color: #155c14;
    }

    .input-group button {
        border-radius: 8px;
        background-color: #f1f1f1;
        border: none;
        padding: 8px 12px;
        margin-left: -1px;
        cursor: pointer;
    }

    .input-group button:hover {
        background-color: #ddd;
    }

    .mb-3 {
        margin-bottom: 20px;
    }

    .header-custom {
        background-color: #0C2D0B;
        color: white;
        padding: 20px 0;
    }

    .header-custom h1 {
        margin: 0;
    }

    @media (max-width: 1200px) {
        .container {
            padding: 20px;
        }

        h1 {
            font-size: 2rem;
        }

        h2 {
            font-size: 1.25rem;
        }
    }

    @media (max-width: 992px) {
        .container {
            padding: 15px;
        }

        h1 {
            font-size: 1.75rem;
        }

        h2 {
            font-size: 1.125rem;
        }

        .profile-img {
            width: 100px;
            height: 100px;
        }
    }

    @media (max-width: 768px) {
        .container {
            padding: 10px;
        }

        h1 {
            font-size: 1.5rem;
        }

        h2 {
            font-size: 1rem;
        }

        .profile-img {
            width: 80px;
            height: 80px;
        }

        .header-custom {
            padding: 15px 0;
        }

        .btn-primary {
            padding: 8px 16px;
        }

        .form-control,
        .form-select {
            font-size: 0.9rem;
        }
    }

    @media (max-width: 576px) {
        .container {
            padding: 10px;
        }

        h1 {
            font-size: 1.25rem;
        }

        h2 {
            font-size: 0.875rem;
        }

        .profile-img {
            width: 70px;
            height: 70px;
        }

        .header-custom {
            padding: 10px 0;
        }

        .btn-primary {
            padding: 8px 14px;
        }

        .form-control,
        .form-select {
            font-size: 0.85rem;
        }
    }
</style>

<body>
    <div class="containerk mt-5">
        <div class="header-custom text-center mt-3">
            <h1>Profile Information</h1>
        </div>

        <form method="POST" id="updateProfileForm">
            <h2>Student Information</h2>
            <div class="row">
                <?php foreach ($students_data as $student): ?>
                    <input type="hidden" name="student_id" value="<?= $student['id'] ?>">
                    <div class="col-md-4 mb-3">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" name="first_name" value="<?= $student['first_name'] ?>" class="form-control">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="middle_name" class="form-label">Middle Name</label>
                        <input type="text" name="middle_name" value="<?= $student['middle_name'] ?>" class="form-control">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" name="last_name" value="<?= $student['last_name'] ?>" class="form-control">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label for="birthdate" class="form-label">Birthdate</label>
                        <input type="date" name="birthdate" value="<?= $student['birthdate'] ?>" class="form-control">
                    </div>
                    <label for="age" class="form-label d-none">Age</label>
                    <input type="hidden" name="age" value="<?= $age ?>">

                    <div class="col-md-3 mb-2">
                        <label for="sex" class="form-label">Sex</label>
                        <select name="sex" class="form-select">
                            <option value="Male" <?= $student['sex'] == 'Male' ? 'selected' : '' ?>>Male</option>
                            <option value="Female" <?= $student['sex'] == 'Female' ? 'selected' : '' ?>>Female</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label for="religion" class="form-label">Religion</label>
                        <input type="text" name="religion" value="<?= $student['religion'] ?>" class="form-control">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label for="contact_number" class="form-label">Contact Number</label>
                        <input
                            type="text"
                            name="contact_number"
                            id="contact_number"
                            value="<?= isset($student['contact_number']) ? $student['contact_number'] : '09' ?>"
                            class="form-control"
                            maxlength="11"
                            pattern="09\d{9}"
                            title="Contact number must start with 09 and be 11 digits"
                            required
                            oninput="enforceNumericInput('contact_number')">
                    </div>

                <?php endforeach; ?>
            </div>
            <div style="border-top: 1px solid #ccc; margin-top: 50px">
                <h2>Parents Information</h2>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="mother_name" class="form-label">Mother's Name</label>
                        <input type="text" name="mother_name" class="form-control" value="<?= isset($mothers_data[0]) ? $mothers_data[0]['name'] : '' ?>">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="mother_contact_number" class="form-label">Contact Number</label>
                        <input
                            type="text"
                            name="mother_contact_number"
                            id="mother_contact_number"
                            value="<?= isset($mothers_data[0]) ? $mothers_data[0]['contact_number'] : '09' ?>"
                            class="form-control"
                            maxlength="11"
                            pattern="09\d{9}"
                            title="Contact number must start with 09 and be 11 digits"
                            required
                            oninput="enforceNumericInput('mother_contact_number')">
                    </div>

                    <div class="col-md-2 mb-3">
                        <label for="mother_email" class="form-label">Email</label>
                        <input type="email" name="mother_email" class="form-control" value="<?= isset($mothers_data[0]) ? $mothers_data[0]['email'] : '' ?>">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="mother_occupation" class="form-label">Occupation</label>
                        <input type="text" name="mother_occupation" class="form-control" value="<?= isset($mothers_data[0]) ? $mothers_data[0]['occupation'] : '' ?>">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="mother_address" class="form-label">Address</label>
                        <input type="text" name="mother_address" class="form-control" value="<?= isset($mothers_data[0]) ? $mothers_data[0]['address'] : '' ?>">
                    </div>
                    <div class="col-md-4 mb-3 mt-3">
                        <label for="father_name" class="form-label">Father's Name</label>
                        <input type="text" name="father_name" class="form-control" value="<?= isset($fathers_data[0]) ? $fathers_data[0]['name'] : '' ?>">
                    </div>
                    <div class="col-md-2 mb-3 mt-3">
                        <label for="father_contact_number" class="form-label">Contact Number</label>
                        <input
                            type="text"
                            name="father_contact_number"
                            id="father_contact_number"
                            value="<?= isset($fathers_data[0]) ? $fathers_data[0]['contact_number'] : '09' ?>"
                            class="form-control"
                            maxlength="11"
                            pattern="09\d{9}"
                            title="Contact number must start with 09 and be 11 digits"
                            required
                            oninput="enforceNumericInput('father_contact_number')">
                    </div>

                    <div class="col-md-2 mb-3 mt-3">
                        <label for="father_email" class="form-label">Email</label>
                        <input type="email" name="father_email" class="form-control" value="<?= isset($fathers_data[0]) ? $fathers_data[0]['email'] : '' ?>">
                    </div>
                    <div class="col-md-2 mb-3 mt-3">
                        <label for="father_occupation" class="form-label">Occupation</label>
                        <input type="text" name="father_occupation" class="form-control" value="<?= isset($fathers_data[0]) ? $fathers_data[0]['occupation'] : '' ?>">
                    </div>
                    <div class="col-md-2 mb-3 mt-3">
                        <label for="father_address" class="form-label">Address</label>
                        <input type="text" name="father_address" class="form-control" value="<?= isset($fathers_data[0]) ? $fathers_data[0]['address'] : '' ?>">
                    </div>
                </div>
            </div>

            <div style="border-top: 1px solid #ccc; margin-top: 50px">
                <h2>Account Information</h2>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" value="<?= $user_data['username'] ?>" readonly>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?= $user_data['email'] ?>" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" name="password" id="password" class="form-control" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                    </div>
                </div>

                <button type="submit" name="update_all" class="btn btn-primary" style="width: 100%; margin-top:10px;">Update Profile</button>
            </div>
        </form>
    </div>

</body>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const togglePassword = document.getElementById('togglePassword');
    const password = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');

    togglePassword.addEventListener('click', function() {

        const type = password.type === 'password' ? 'text' : 'password';
        password.type = type;


        if (type === 'password') {
            eyeIcon.classList.remove('bi-eye');
            eyeIcon.classList.add('bi-eye-slash');
        } else {
            eyeIcon.classList.remove('bi-eye-slash');
            eyeIcon.classList.add('bi-eye');
        }
    });

    document.getElementById("updateProfileForm").addEventListener("submit", function(event) {
        const password = document.getElementById("password").value;
        const confirmPassword = document.getElementById("confirm_password").value;

        if (password !== confirmPassword) {
            event.preventDefault();
            alert("Passwords do not match!");
        }
    });
</script>
<script>
    function enforceNumericInput(inputId) {
        const contactInput = document.getElementById(inputId);
        // Ensure only numeric input
        contactInput.value = contactInput.value.replace(/[^0-9]/g, '');
        // Enforce prefix '09'
        if (!contactInput.value.startsWith('09')) {
            contactInput.value = '09';
        }
        // Limit length to 11 digits
        if (contactInput.value.length > 11) {
            contactInput.value = contactInput.value.slice(0, 11);
        }
    }
</script>

</body>

</html>