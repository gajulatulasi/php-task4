<?php
session_start();
require 'config.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['username']) && isset($_POST['password'])) {
        // Sanitize inputs
        $username = trim(htmlspecialchars($_POST['username']));
        $password = $_POST['password'];

        // Validation
        if (empty($username) || empty($password)) {
            $error = "Please enter username and password!";
        } else {
            // Use PDO with prepared statements
            $stmt = $pdo->prepare("SELECT id, username, password, role FROM users WHERE username=?");
            $stmt->execute([$username]);
            
            if ($stmt->rowCount() === 1) {
                $user = $stmt->fetch();
                
                if (password_verify($password, $user['password'])) {
                    // Regenerate session ID to prevent fixation
                    session_regenerate_id(true);
                    
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['user_role'] = $user['role'];
                    
                    header("Location: index.php");
                    exit;
                } else {
                    $error = "Invalid username or password!";
                }
            } else {
                $error = "Invalid username or password!";
            }
        }
    } else {
        $error = "Please enter username and password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f0f2f5;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }
    .login-container {
      width: 350px;
      padding: 30px;
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      text-align: center;
    }
    .login-container h2 {
      margin-bottom: 20px;
      color: #333;
    }
    .input-group {
      margin-bottom: 15px;
      text-align: left;
    }
    .input-group label {
      display: block;
      font-size: 14px;
      margin-bottom: 5px;
      color: #555;
    }
    .input-group input {
      width: 100%;
      padding: 10px;
      border-radius: 8px;
      border: 1px solid #ccc;
      outline: none;
      transition: 0.3s;
      box-sizing: border-box;
    }
    .input-group input:focus {
      border-color: #007bff;
    }
    .btn {
      width: 100%;
      padding: 12px;
      border: none;
      border-radius: 8px;
      background: #007bff;
      color: #fff;
      font-size: 16px;
      cursor: pointer;
      transition: 0.3s;
    }
    .btn:hover {
      background: #0056b3;
    }
    .error {
      margin-top: 10px;
      color: red;
      font-size: 14px;
      background: #ffebee;
      padding: 10px;
      border-radius: 5px;
      border: 1px solid #ffcdd2;
    }
    .success {
      margin-top: 10px;
      color: green;
      font-size: 14px;
      background: #e8f5e8;
      padding: 10px;
      border-radius: 5px;
      border: 1px solid #c8e6c9;
    }
    .links {
      margin-top: 15px;
      font-size: 14px;
    }
    .links a {
      color: #007bff;
      text-decoration: none;
    }
    .links a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>

<div class="login-container">
  <h2>Login</h2>
  
  <?php if (isset($_SESSION['success'])): ?>
    <div class="success"><?= $_SESSION['success'] ?></div>
    <?php unset($_SESSION['success']); ?>
  <?php endif; ?>
  
  <form method="POST">
    <div class="input-group">
      <label for="username">Username</label>
      <input type="text" name="username" required 
             value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>">
    </div>
    <div class="input-group">
      <label for="password">Password</label>
      <input type="password" name="password" required>
    </div>
    <button type="submit" class="btn">Login</button>
    <?php if (isset($error)) { echo "<div class='error'>$error</div>"; } ?>
  </form>
  <div class="links">
    <p>Don't have an account? <a href="register.php">Register</a></p>
  </div>
</div>

</body>
</html>