<div class="w3-sidebar w3-bar-block w3-card w3-animate-left gc-green text-success" style="display:none; position: relative;" id="mySidebar">
    <button class="w3-bar-item w3-button w3-xlarge text-success text-right" onclick="w3_close()">&times;</button>

    <!-- Dashboard -->
    <a href="dashboard.php" class="w3-bar-item w3-button text-success text-decoration-none">
        <i class="fas fa-tachometer-alt m-3"></i> Dashboard
    </a>

    <!-- Complaints with Dropdown -->
    <a class="w3-bar-item w3-button text-success text-decoration-none d-flex justify-content-between" data-toggle="collapse" href="#complaints" role="button" aria-expanded="false" aria-controls="complaints">
        <span><i class="fas fa-exclamation-triangle m-3"></i> Complaints</span>
        <i class="fas fa-chevron-down toggle-icon mt-3"></i>
    </a>

    <div class="collapse" id="complaints">
        <a href="complaint_teacher.php" class="w3-bar-item w3-button text-success pl-5 ml-5 text-decoration-none"><i class="fas fa-chalkboard-teacher mr-2"></i> School Personnel</a>
        <a href="complaint_student.php" class="w3-bar-item w3-button text-success pl-5 ml-5 text-decoration-none"><i class="fas fa-user-graduate mr-2"></i> Student</a>
    </div>

    <!-- Complaints with Dropdown -->
    <a class="w3-bar-item w3-button text-success text-decoration-none d-flex justify-content-between" data-toggle="collapse" href="#calendars" role="button" aria-expanded="false" aria-controls="complaints">
        <span><i class="fas fa-calendar-alt m-3"></i> Calendar</span>
        <i class="fas fa-chevron-down toggle-icon mt-3"></i>
    </a>

    <div class="collapse" id="calendars">
        <a href="calendar.php" class="w3-bar-item w3-button text-success pl-5 ml-5 text-decoration-none">
            <i class="fas fa-calendar-check mr-2"></i> Schedule
        </a>
        <a href="counseling.php" class="w3-bar-item w3-button text-success pl-5 ml-5 text-decoration-none">
            <i class="fas fa-file-alt mr-2"></i> Requests
        </a>
    </div>


    <!-- Violators -->
    <a href="violators.php" class="w3-bar-item w3-button text-success text-decoration-none">
        <i class="fas fa-user-times m-3"></i> Violators
    </a>

    <!-- Users with Dropdown -->
    <a class="w3-bar-item w3-button text-success text-decoration-none d-flex justify-content-between" data-toggle="collapse" href="#users" role="button" aria-expanded="false" aria-controls="users">
        <span><i class="fas fa-users m-3"></i> Users</span>
        <i class="fas fa-chevron-down toggle-icon mt-3"></i>
    </a>
    <div class="collapse" id="users">
        <a href="admin_page.php" class="w3-bar-item w3-button text-success pl-5 ml-5 text-decoration-none"><i class="fas fa-user-shield mr-2"></i> Admins</a>
        <a href="student_page.php" class="w3-bar-item w3-button text-success pl-5 ml-5 text-decoration-none"><i class="fas fa-user-graduate mr-2"></i> Students</a>
        <a href="teacher_page.php" class="w3-bar-item w3-button text-success pl-5 ml-5 text-decoration-none"><i class="fas fa-chalkboard-teacher mr-2"></i> Teachers</a>
        <a href="guard_page.php" class="w3-bar-item w3-button text-success pl-5 ml-5 text-decoration-none"><i class="fas fa-shield-alt mr-2"></i> Guards</a>
    </div>

    <!-- School with Dropdown -->
    <a class="w3-bar-item w3-button text-success text-decoration-none d-flex justify-content-between" data-toggle="collapse" href="#school" role="button" aria-expanded="false" aria-controls="school">
        <span><i class="fas fa-school m-3"></i> School</span>
        <i class="fas fa-chevron-down toggle-icon mt-3"></i>
    </a>
    <div class="collapse" id="school">
        <a href="strands.php" class="w3-bar-item w3-button text-success pl-5 ml-5 text-decoration-none"><i class="fas fa-layer-group mr-2"></i> Strands</a>
        <a href="sections.php" class="w3-bar-item w3-button text-success pl-5 ml-5 text-decoration-none"><i class="fas fa-sitemap mr-2"></i> Sections</a>
        <a href="school_year.php" class="w3-bar-item w3-button text-success pl-5 ml-5 text-decoration-none"><i class="fas fa-calendar-alt mr-2"></i> School Years</a>
    </div>

    <!-- Settings -->
    <a href="settings.php" class="w3-bar-item w3-button text-success text-decoration-none">
        <i class="fas fa-cogs m-3"></i> Settings
    </a>

    <!-- Logout button positioned at the bottom -->
    <a href="#" class="w3-bar-item w3-button text-success text-decoration-none" style="position: absolute; bottom: 0; width: 100%;" data-bs-toggle="modal" data-bs-target="#logoutModal">
        <i class="fas fa-sign-out-alt m-3"></i> Logout
    </a>
</div>


<!-- Logout Confirmation Modal -->
<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content w3-white text-center" style="width: 300px; height: 200px; margin: auto; border-radius: 15px;">
            <div class="modal-header border-0">
                <div class="w-100">
                    <!-- Leaving-themed icon -->
                    <i class="fas fa-sad-tear fa-3x text-warning"></i>
                </div>
            </div>
            <div class="modal-body">
                <h5 class="mt-2">Are you sure you want to log out?</h5>
            </div>
            <div class="modal-footer justify-content-around border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <!-- Link to logout.php -->
                <a href="logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>
    </div>
</div>