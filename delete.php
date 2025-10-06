<?php
require 'config.php';
if(!isset($_SESSION['user_id'])){ 
    header("Location: login.php"); 
    exit; 
}

// Validate and sanitize ID
$id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT);
if (!$id) {
    header("Location: index.php");
    exit;
}

// Get post using prepared statement
$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->execute([$id]);
$post = $stmt->fetch();

if(!$post){
    header("Location: index.php");
    exit;
}

// Authorization check - user can only delete their own posts unless admin
if($post['user_id'] != $_SESSION['user_id'] && $_SESSION['user_role'] != 'admin'){
    header("Location: index.php?msg=Unauthorized+access");
    exit;
}

// If confirmed delete
if(isset($_POST['confirm_delete'])){
    $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
    if($stmt->execute([$id])){
        header("Location: index.php?msg=Post+deleted+successfully");
        exit;
    } else {
        $error = "Failed to delete post. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete Post</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .card {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(12px);
            border: none;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
            color: white;
            width: 100%;
            max-width: 500px;
        }
        .btn-danger {
            background: linear-gradient(45deg, #ff5e62, #ff9966);
            border: none;
        }
        .btn-danger:hover {
            background: linear-gradient(45deg, #ff3838, #ff5e62);
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="card p-5 text-center">
            <h2 class="text-warning mb-4">⚠️ Confirm Delete</h2>
            
            <?php if(isset($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <p class="mb-4">
                Are you sure you want to delete this post?<br>
                <strong class="text-warning">"<?= htmlspecialchars($post['title']); ?>"</strong>
            </p>

            <form method="post" class="d-flex justify-content-center gap-3">
                <button type="submit" name="confirm_delete" 
                    class="btn btn-danger px-4 py-2">
                    Yes, Delete
                </button>
                <a href="index.php" 
                   class="btn btn-outline-light px-4 py-2">
                   Cancel
                </a>
            </form>
        </div>
    </div>

</body>
</html>