<?php
session_start();
require "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize inputs
    $first    = trim(htmlspecialchars($_POST['first_name']));
    $last     = trim(htmlspecialchars($_POST['last_name']));
    $username = trim(htmlspecialchars($_POST['username']));
    $email    = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $pass     = $_POST['password'];
    $cpass    = $_POST['confirm_password'];

    $errors = [];

    // Validation
    if (empty($first) || empty($last) || empty($username) || empty($email) || empty($pass)) {
        $errors[] = "All fields are required!";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format!";
    }

    if (strlen($pass) < 6) {
        $errors[] = "Password must be at least 6 characters!";
    }

    if ($pass !== $cpass) {
        $errors[] = "Passwords do not match!";
    }

    if (strlen($username) < 3) {
        $errors[] = "Username must be at least 3 characters!";
    }

    if (empty($errors)) {
        // Check if user exists using PDO
        $check = $pdo->prepare("SELECT id FROM users WHERE email=? OR username=?");
        $check->execute([$email, $username]);
        
        if ($check->rowCount() > 0) {
            $errors[] = "Email or username already registered!";
        } else {
            $hash = password_hash($pass, PASSWORD_BCRYPT);

            // Insert using PDO
            $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, username, email, password, role) VALUES (?,?,?,?,?, 'user')");
            
            if ($stmt->execute([$first, $last, $username, $email, $hash])) {
                $_SESSION['success'] = "Registration successful! Please login.";
                header("Location: login.php");
                exit;
            } else {
                $errors[] = "Something went wrong. Try again.";
            }
        }
    }
    
    if (!empty($errors)) {
        $error = implode("<br>", $errors);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #4b6cb7;
            background: linear-gradient(to right, #182848, #4b6cb7);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .register-box {
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            width: 350px;
            box-shadow: 0px 4px 12px rgba(0,0,0,0.2);
        }
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 15px;
        }
        input[type="text"], input[type="email"], input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }
        .terms {
            font-size: 12px;
            color: #555;
            margin: 10px 0;
        }
        .btn {
            width: 100%;
            padding: 10px;
            background: linear-gradient(to right, #56ab2f, #a8e063);
            border: none;
            color: #fff;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn:hover {
            opacity: 0.9;
        }
        .login-link {
            text-align: center;
            margin-top: 10px;
            font-size: 14px;
        }
        .error {
            background: #ffdddd;
            border: 1px solid #ff5c5c;
            padding: 8px;
            color: #a00;
            margin-bottom: 10px;
            border-radius: 5px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="register-box">
        <h2>Register</h2>
        <?php if (isset($error)) echo "<div class='error'>$error</div>"; ?>
        <form method="POST" action="">
            <input type="text" name="first_name" placeholder="First Name" 
                   value="<?= isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : '' ?>" 
                   required minlength="2">
            <input type="text" name="last_name" placeholder="Last Name" 
                   value="<?= isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : '' ?>" 
                   required minlength="2">
            <input type="text" name="username" placeholder="Username" 
                   value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>" 
                   required minlength="3">
            <input type="email" name="email" placeholder="Email"
                   value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" 
                   required>
            <input type="password" name="password" placeholder="Password" required minlength="6">
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            
            <div class="terms">
                <input type="checkbox" required> I accept the <a href="#">Terms of Use</a> & <a href="#">Privacy Policy</a>.
            </div>
            
            <button type="submit" class="btn">Register Now</button>
        </form>
        <div class="login-link">
            Already have an account? <a href="login.php">Sign in</a>
        </div>
    </div>
</body>
</html>