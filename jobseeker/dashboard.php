<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'job_seeker') {
    header("Location: ../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Seeker Dashboard - HireVo</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <nav>
        <div class="container">
            <div class="logo">HireVo</div>
            <div class="auth-buttons user-nav">
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="../login.php" class="btn btn-login">Logout</a>
                <div class="profile-menu" id="profile-menu">
                    <button type="button" class="profile-trigger" aria-expanded="false" aria-controls="profile-dropdown" aria-label="Open profile menu">
                        <span class="profile-avatar"><?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?></span>
                    </button>
                    <div class="profile-dropdown" id="profile-dropdown" role="menu">
                        <a href="#" role="menuitem">Edit Profile</a>
                        <a href="#" role="menuitem">Change Password</a>
                        <a href="#" role="menuitem">Account Settings</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    <div class="container" style="margin-top: 2rem;">
        <div class="form-container" style="max-width: 800px;">
            <h2>Job Seeker Dashboard</h2>
            <p>Welcome to your career portal. Here you will be able to search for jobs and manage your profile.</p>
        </div>
    </div>
    <script src="../assets/js/dashboard-menu.js"></script>
</body>
</html>
