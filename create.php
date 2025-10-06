<?php
require 'config.php';
if(!isset($_SESSION['user_id'])){ 
    header("Location: login.php"); 
    exit; 
}

$errors = [];
$success = "";

if($_POST){
    // Sanitize inputs
    $title   = trim(htmlspecialchars($_POST['title']));
    $content = trim(htmlspecialchars($_POST['content']));
    
    // Server-side validation
    if(empty($title)) {
        $errors[] = "Title is required";
    } elseif(strlen($title) < 3) {
        $errors[] = "Title must be at least 3 characters";
    } elseif(strlen($title) > 255) {
        $errors[] = "Title must be less than 255 characters";
    }
    
    if(empty($content)) {
        $errors[] = "Content is required";
    } elseif(strlen($content) < 10) {
        $errors[] = "Content must be at least 10 characters";
    }
    
    if(empty($errors)){
        // Use prepared statement
        $stmt = $pdo->prepare("INSERT INTO posts (title, content, user_id) VALUES (?, ?, ?)");
        
        if($stmt->execute([$title, $content, $_SESSION['user_id']])){
            header("Location: index.php?msg=Post+created+successfully");
            exit;
        } else {
            $errors[] = "Failed to create post. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
 <title>Create Post</title>
 <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
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
    max-width: 600px;
  }
  .form-control {
    background: rgba(255,255,255,0.2);
    border: none;
    border-radius: 10px;
    color: white;
  }
  .form-control::placeholder {
    color: #ddd;
  }
  .form-control:focus {
    background: rgba(255,255,255,0.25);
    color: white;
    box-shadow: none;
  }
  textarea {
    min-height: 150px;
  }
  .btn-gradient {
    background: linear-gradient(45deg, #42e695, #3bb2b8);
    border: none;
    color: white;
    font-weight: 500;
    padding: 10px 20px;
    border-radius: 30px;
    transition: 0.3s;
  }
  .btn-gradient:hover {
    background: linear-gradient(45deg, #3bb2b8, #42e695);
    transform: scale(1.05);
    color: white;
  }
  .btn-back {
    position: absolute;
    top: 20px;
    left: 20px;
  }
  .alert-danger {
    background: rgba(255, 0, 0, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.3);
    color: white;
  }
 </style>
</head>
<body>
<div class="container">
  <a href="index.php" class="btn btn-outline-light btn-back">⬅ Back</a>
  
  <div class="card p-4">
    <h3 class="text-center mb-3">📝 Create New Post</h3>
    
    <?php if(!empty($errors)): ?>
      <div class="alert alert-danger">
        <strong>Please fix the following errors:</strong>
        <ul class="mb-0">
          <?php foreach($errors as $error): ?>
            <li><?= htmlspecialchars($error) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>
    
    <form method="POST">
      <div class="mb-3">
        <input class="form-control" type="text" name="title" placeholder="Enter title..." 
               value="<?= isset($_POST['title']) ? htmlspecialchars($_POST['title']) : '' ?>" 
               required minlength="3" maxlength="255">
        <small class="text-light">Minimum 3 characters, maximum 255 characters</small>
      </div>
      
      <div class="mb-3">
        <textarea class="form-control" name="content" placeholder="Write your content here..." 
                  required minlength="10"><?= isset($_POST['content']) ? htmlspecialchars($_POST['content']) : '' ?></textarea>
        <small class="text-light">Minimum 10 characters</small>
      </div>
      
      <div class="d-grid">
        <button class="btn btn-gradient">✅ Publish Post</button>
      </div>
    </form>
  </div>
</div>
</body>
</html>