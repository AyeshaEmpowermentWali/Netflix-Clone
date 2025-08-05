<?php
require_once 'db.php';

if (!is_user_logged_in()) {
    redirect_to('login.php');
}

$current_user = get_logged_user();
$success = '';
$error = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize_input($_POST['username']);
    $email = sanitize_input($_POST['email']);
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($username) || empty($email)) {
        $error = 'Username and email are required.';
    } else {
        // Check if username/email already exists for other users
        $stmt = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
        $stmt->execute([$username, $email, $current_user['id']]);
        
        if ($stmt->fetch()) {
            $error = 'Username or email already exists.';
        } else {
            // Update basic info
            $update_sql = "UPDATE users SET username = ?, email = ? WHERE id = ?";
            $update_params = [$username, $email, $current_user['id']];
            
            // Handle password change
            if (!empty($new_password)) {
                if (empty($current_password)) {
                    $error = 'Current password is required to change password.';
                } elseif (!password_verify($current_password, $current_user['password'])) {
                    $error = 'Current password is incorrect.';
                } elseif ($new_password !== $confirm_password) {
                    $error = 'New passwords do not match.';
                } elseif (strlen($new_password) < 6) {
                    $error = 'New password must be at least 6 characters long.';
                } else {
                    $update_sql = "UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?";
                    $update_params = [$username, $email, password_hash($new_password, PASSWORD_DEFAULT), $current_user['id']];
                }
            }
            
            if (empty($error)) {
                $stmt = $pdo->prepare($update_sql);
                if ($stmt->execute($update_params)) {
                    $success = 'Profile updated successfully!';
                    // Refresh user data
                    $current_user = get_logged_user();
                } else {
                    $error = 'Failed to update profile.';
                }
            }
        }
    }
}

// Get user statistics
$stats_stmt = $pdo->prepare("
    SELECT 
        (SELECT COUNT(*) FROM watchlist WHERE user_id = ?) as watchlist_count,
        (SELECT COUNT(*) FROM watch_progress WHERE user_id = ?) as watched_count,
        (SELECT COUNT(*) FROM user_ratings WHERE user_id = ?) as ratings_count
");
$stats_stmt->execute([$current_user['id'], $current_user['id'], $current_user['id']]);
$user_stats = $stats_stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Netflix Clone</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            background-color: #141414;
            color: white;
            min-height: 100vh;
        }

        /* Header Styles */
        .header {
            position: fixed;
            top: 0;
            width: 100%;
            background-color: #141414;
            z-index: 1000;
            padding: 20px 4%;
            border-bottom: 1px solid #333;
        }

        .nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 2rem;
            font-weight: bold;
            color: #e50914;
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 2rem;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            transition: color 0.3s;
        }

        .nav-links a:hover {
            color: #b3b3b3;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .profile-btn {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            padding: 8px 16px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .profile-btn:hover {
            background-color: rgba(255,255,255,0.1);
        }

        /* Main Content */
        .main-content {
            padding: 120px 4% 2rem;
            max-width: 800px;
            margin: 0 auto;
        }

        .profile-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(45deg, #e50914, #f40612);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            font-weight: bold;
            margin: 0 auto 1rem;
        }

        .profile-name {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .profile-email {
            color: #b3b3b3;
            font-size: 1.1rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .stat-card {
            background: rgba(255,255,255,0.1);
            padding: 2rem;
            border-radius: 8px;
            text-align: center;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #e50914;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #b3b3b3;
            font-size: 1rem;
        }

        .profile-form {
            background: rgba(255,255,255,0.05);
            padding: 2rem;
            border-radius: 8px;
            margin-bottom: 2rem;
        }

        .form-title {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 1rem;
            border: none;
            border-radius: 4px;
            background: #333;
            color: white;
            font-size: 1rem;
            transition: background-color 0.3s;
        }

        .form-group input:focus {
            outline: none;
            background: #555;
        }

        .form-group input::placeholder {
            color: #999;
        }

        .form-section {
            border-top: 1px solid #333;
            padding-top: 2rem;
            margin-top: 2rem;
        }

        .form-section:first-child {
            border-top: none;
            padding-top: 0;
            margin-top: 0;
        }

        .error-message {
            background: #e50914;
            color: white;
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
            text-align: center;
        }

        .success-message {
            background: #46d369;
            color: white;
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
            text-align: center;
        }

        .btn {
            padding: 1rem 2rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: bold;
            transition: all 0.3s;
            margin-right: 1rem;
        }

        .btn-primary {
            background: #e50914;
            color: white;
        }

        .btn-primary:hover {
            background: #f40612;
        }

        .btn-secondary {
            background: rgba(255,255,255,0.2);
            color: white;
        }

        .btn-secondary:hover {
            background: rgba(255,255,255,0.3);
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }

            .main-content {
                padding: 100px 2% 1rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn {
                margin-right: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="nav">
            <a href="index.php" class="logo">NETFLIX</a>
            
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="search.php">Browse</a></li>
                <li><a href="watchlist.php">My List</a></li>
            </ul>

            <div class="user-menu">
                <span>Welcome, <?php echo htmlspecialchars($current_user['username']); ?></span>
                <button class="profile-btn" onclick="redirectTo('profile.php')">Profile</button>
                <button class="profile-btn" onclick="redirectTo('logout.php')">Logout</button>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Profile Header -->
        <div class="profile-header">
            <div class="profile-avatar">
                <?php echo strtoupper(substr($current_user['username'], 0, 1)); ?>
            </div>
            <h1 class="profile-name"><?php echo htmlspecialchars($current_user['username']); ?></h1>
            <p class="profile-email"><?php echo htmlspecialchars($current_user['email']); ?></p>
        </div>

        <!-- User Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $user_stats['watchlist_count']; ?></div>
                <div class="stat-label">Items in Watchlist</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $user_stats['watched_count']; ?></div>
                <div class="stat-label">Videos Watched</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $user_stats['ratings_count']; ?></div>
                <div class="stat-label">Ratings Given</div>
            </div>
        </div>

        <!-- Profile Form -->
        <div class="profile-form">
            <h2 class="form-title">Edit Profile</h2>

            <?php if ($error): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="success-message"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <!-- Basic Information -->
                <div class="form-section">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" 
                               value="<?php echo htmlspecialchars($current_user['username']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" 
                               value="<?php echo htmlspecialchars($current_user['email']); ?>" required>
                    </div>
                </div>

                <!-- Password Change -->
                <div class="form-section">
                    <h3>Change Password</h3>
                    <p style="color: #b3b3b3; margin-bottom: 1rem;">Leave blank to keep current password</p>

                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password" 
                               placeholder="Enter current password">
                    </div>

                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" 
                               placeholder="Enter new password">
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" 
                               placeholder="Confirm new password">
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Update Profile</button>
                    <button type="button" class="btn btn-secondary" onclick="resetForm()">Reset</button>
                </div>
            </form>
        </div>

        <!-- Account Actions -->
        <div class="profile-form">
            <h2 class="form-title">Account Actions</h2>
            <div class="form-actions">
                <button class="btn btn-secondary" onclick="redirectTo('watchlist.php')">View My List</button>
                <button class="btn btn-secondary" onclick="clearWatchHistory()">Clear Watch History</button>
            </div>
        </div>
    </main>

    <script>
        function redirectTo(url) {
            window.location.href = url;
        }

        function resetForm() {
            document.querySelector('form').reset();
            // Reset to original values
            document.getElementById('username').value = '<?php echo htmlspecialchars($current_user['username']); ?>';
            document.getElementById('email').value = '<?php echo htmlspecialchars($current_user['email']); ?>';
        }

        function clearWatchHistory() {
            if (confirm('Are you sure you want to clear your watch history? This action cannot be undone.')) {
                fetch('clear_history.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Watch history cleared successfully!');
                        location.reload();
                    } else {
                        alert('Failed to clear watch history.');
                    }
                });
            }
        }

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const currentPassword = document.getElementById('current_password').value;

            if (newPassword && !currentPassword) {
                e.preventDefault();
                alert('Current password is required to change password.');
                return;
            }

            if (newPassword && newPassword !== confirmPassword) {
                e.preventDefault();
                alert('New passwords do not match.');
                return;
            }

            if (newPassword && newPassword.length < 6) {
                e.preventDefault();
                alert('New password must be at least 6 characters long.');
                return;
            }
        });
    </script>
</body>
</html>
