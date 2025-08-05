<?php
require_once 'db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize_input($_POST['username']);
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all fields.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } else {
        // Check if user already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$email, $username]);
        
        if ($stmt->fetch()) {
            $error = 'User with this email or username already exists.';
        } else {
            // Create new user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            
            if ($stmt->execute([$username, $email, $hashed_password])) {
                $success = 'Account created successfully! You can now sign in.';
            } else {
                $error = 'Failed to create account. Please try again.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Netflix Clone</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');
            background-size: cover;
            background-position: center;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .signup-container {
            background: rgba(0,0,0,0.8);
            padding: 3rem;
            border-radius: 8px;
            width: 100%;
            max-width: 400px;
            backdrop-filter: blur(10px);
        }

        .logo {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo a {
            font-size: 2.5rem;
            font-weight: bold;
            color: #e50914;
            text-decoration: none;
        }

        .form-title {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 2rem;
            text-align: center;
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
            width: 100%;
            padding: 1rem;
            border: none;
            border-radius: 4px;
            background: #e50914;
            color: white;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-bottom: 1rem;
        }

        .btn:hover {
            background: #f40612;
        }

        .form-links {
            text-align: center;
        }

        .form-links a {
            color: #b3b3b3;
            text-decoration: none;
            transition: color 0.3s;
        }

        .form-links a:hover {
            color: white;
        }

        .divider {
            text-align: center;
            margin: 1.5rem 0;
            color: #b3b3b3;
        }

        .back-home {
            position: absolute;
            top: 20px;
            left: 20px;
        }

        .back-home a {
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            background: rgba(0,0,0,0.5);
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .back-home a:hover {
            background: rgba(0,0,0,0.8);
        }

        .password-strength {
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }

        .strength-weak { color: #e50914; }
        .strength-medium { color: #ffa500; }
        .strength-strong { color: #46d369; }

        @media (max-width: 480px) {
            .signup-container {
                padding: 2rem;
                margin: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="back-home">
        <a href="index.php">← Back to Home</a>
    </div>

    <div class="signup-container">
        <div class="logo">
            <a href="index.php">NETFLIX</a>
        </div>

        <h1 class="form-title">Sign Up</h1>

        <?php if ($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success-message"><?php echo $success; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Choose a username" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Create a password" required>
                <div class="password-strength" id="passwordStrength"></div>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
            </div>

            <button type="submit" class="btn">Sign Up</button>
        </form>

        <div class="divider">or</div>

        <div class="form-links">
            <p>Already have an account? <a href="login.php">Sign in</a></p>
        </div>
    </div>

    <script>
        // Password strength checker
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthDiv = document.getElementById('passwordStrength');
            
            if (password.length === 0) {
                strengthDiv.textContent = '';
                return;
            }
            
            let strength = 0;
            if (password.length >= 6) strength++;
            if (password.match(/[a-z]/)) strength++;
            if (password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;
            
            if (strength < 3) {
                strengthDiv.textContent = 'Weak password';
                strengthDiv.className = 'password-strength strength-weak';
            } else if (strength < 4) {
                strengthDiv.textContent = 'Medium password';
                strengthDiv.className = 'password-strength strength-medium';
            } else {
                strengthDiv.textContent = 'Strong password';
                strengthDiv.className = 'password-strength strength-strong';
            }
        });

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const username = document.getElementById('username').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (!username || !email || !password || !confirmPassword) {
                e.preventDefault();
                alert('Please fill in all fields.');
                return;
            }

            if (!isValidEmail(email)) {
                e.preventDefault();
                alert('Please enter a valid email address.');
                return;
            }

            if (password.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long.');
                return;
            }

            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match.');
                return;
            }
        });

        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }
    </script>
</body>
</html>
