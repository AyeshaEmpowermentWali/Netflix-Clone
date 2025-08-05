<?php
require_once 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            redirect_to('index.php'); // UPDATED function name
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - Netflix Clone</title>
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

        .login-container {
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

        @media (max-width: 480px) {
            .login-container {
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

    <div class="login-container">
        <div class="logo">
            <a href="index.php">NETFLIX</a>
        </div>

        <h1 class="form-title">Sign In</h1>

        <?php if ($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
            </div>

            <button type="submit" class="btn">Sign In</button>
        </form>

        <div class="divider">or</div>

        <div class="form-links">
            <p>New to Netflix? <a href="signup.php">Sign up now</a></p>
        </div>
    </div>
</body>
</html>
