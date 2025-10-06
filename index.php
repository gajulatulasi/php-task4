<?php
require 'config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Security: Input validation
$keyword = isset($_GET['search']) ? trim(htmlspecialchars($_GET['search'])) : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

// Use prepared statements to prevent SQL injection
if (!empty($keyword)) {
    $query = "SELECT p.*, u.username as author_name 
              FROM posts p 
              LEFT JOIN users u ON p.user_id = u.id 
              WHERE p.title LIKE :keyword OR p.content LIKE :keyword 
              ORDER BY p.created_at DESC 
              LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($query);
    $searchTerm = "%$keyword%";
    $stmt->bindParam(':keyword', $searchTerm, PDO::PARAM_STR);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $posts = $stmt->fetchAll();
} else {
    $query = "SELECT p.*, u.username as author_name 
              FROM posts p 
              LEFT JOIN users u ON p.user_id = u.id 
              ORDER BY p.created_at DESC 
              LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $posts = $stmt->fetchAll();
}

// Count total posts for pagination
if (!empty($keyword)) {
    $countQuery = "SELECT COUNT(*) as total FROM posts WHERE title LIKE :keyword OR content LIKE :keyword";
    $countStmt = $pdo->prepare($countQuery);
    $countStmt->bindParam(':keyword', $searchTerm, PDO::PARAM_STR);
    $countStmt->execute();
} else {
    $countQuery = "SELECT COUNT(*) as total FROM posts";
    $countStmt = $pdo->prepare($countQuery);
    $countStmt->execute();
}

$totalRows = $countStmt->fetch()['total'];
$totalPages = ceil($totalRows / $limit);
?>
<!DOCTYPE html>
<html>
<head>
  <title>Blog</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <style>
    body {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      min-height: 100vh;
      font-family: 'Segoe UI', sans-serif;
    }
    .navbar {
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(10px);
      border-radius: 15px;
    }
    .card {
      border: none;
      border-radius: 15px;
      box-shadow: 0px 5px 15px rgba(0,0,0,0.15);
      transition: transform 0.2s;
    }
    .card:hover {
      transform: translateY(-5px);
    }
    .page-link {
      border-radius: 50% !important;
      margin: 0 5px;
    }
    .btn-gradient {
      background: linear-gradient(45deg, #42e695, #3bb2b8);
      border: none;
      color: white;
      transition: 0.3s;
    }
    .btn-gradient:hover {
      background: linear-gradient(45deg, #3bb2b8, #42e695);
      color: white;
    }
    .author-badge {
      background: linear-gradient(45deg, #ff6b6b, #ee5a24);
      color: white;
      padding: 2px 8px;
      border-radius: 10px;
      font-size: 0.8em;
    }
  </style>
</head>
<body>
  <div class="container py-4">

    <nav class="navbar navbar-expand-lg p-3 mb-4 shadow-sm">
      <div class="container-fluid">
        <h3 class="text-white mb-0">My Blog</h3>
        <div>
          <span class="text-white me-3">
            Welcome, <?= htmlspecialchars($_SESSION['username']) ?> 
            <span class="author-badge"><?= $_SESSION['user_role'] ?></span>
          </span>
          <a href="create.php" class="btn btn-gradient me-2">➕ New Post</a>
          <a href="logout.php" class="btn btn-outline-light">Logout</a>
        </div>
      </div>
    </nav>

    <!-- Success Message -->
    <?php if(isset($_GET['msg'])): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_GET['msg']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <form class="d-flex mb-4" method="GET">
      <input type="text" class="form-control me-2" name="search" placeholder="🔍 Search posts..."
             value="<?= htmlspecialchars($keyword) ?>">
      <button class="btn btn-light">Search</button>
    </form>

    <?php if (count($posts) > 0): ?>
      <?php foreach ($posts as $row): ?>
        <div class="card mb-4">
          <div class="card-body">
            <h4 class="card-title text-primary"><?= htmlspecialchars($row['title']); ?></h4>
            <p class="card-text"><?= nl2br(htmlspecialchars(substr($row['content'], 0, 200))); ?>...</p>
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <small class="text-muted">
                  Posted on <?= date("M d, Y", strtotime($row['created_at'])); ?>
                  <?php if($row['author_name']): ?>
                    by <span class="author-badge"><?= htmlspecialchars($row['author_name']) ?></span>
                  <?php endif; ?>
                </small>
              </div>
              <div>
                <!-- Role-based access control -->
                <?php if ($_SESSION['user_id'] == $row['user_id'] || $_SESSION['user_role'] == 'admin'): ?>
                  <a href="edit.php?id=<?= $row['id']; ?>" class="btn btn-warning btn-sm">✏️ Edit</a>
                  <a href="delete.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm" 
                     onclick="return confirm('Are you sure you want to delete this post?')">🗑️ Delete</a>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="alert alert-light text-center shadow-sm">No posts found!</div>
    <?php endif; ?>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
      <nav class="d-flex justify-content-center">
        <ul class="pagination">
          <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item <?= ($i == $page ? 'active' : '') ?>">
              <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($keyword) ?>"><?= $i ?></a>
            </li>
          <?php endfor; ?>
        </ul>
      </nav>
    <?php endif; ?>

  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>