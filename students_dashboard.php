<?php
session_start();
require 'dbcon.php';

if (empty($_SESSION['user_id']) || (($_SESSION['role'] ?? '') !== 'student')) {
  header('Location: login.php');
  exit;
}

$uid = $_SESSION['user_id'];

$stmt = mysqli_prepare($connection, "SELECT user_id, name FROM users WHERE user_id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "s", $uid);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $user_id, $stud_name);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

$user_id = $user_id ?? $uid;
$stud_name = $stud_name ?? 'Student';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Dashboard</title>
  <link rel="stylesheet" href="css/index.css">
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

</head>
<body>

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid px-4">
    <img src="img/pup_logo.png" alt="" style="height: 40px; width: auto; margin-right: 10px;">
      <a class="navbar-brand fw-bold" href="#">Student Dashboard</a>
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link active" href="students_dashboard.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="books.php">Books</a></li>
        <li class="nav-item"><a class="nav-link" href="borrowed_books.php">Borrowed</a></li>
        <li class="nav-item"><a class="nav-link" href="student_profile.php">Profile</a></li>
        <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </nav>

<!-- Main Content -->
<main class="container my-5">
  <div class="bg-light p-4 rounded-3 mb-4 shadow-sm">
    <h2 class="fw-bold mb-1">Welcome back, <?= htmlspecialchars($stud_name) ?>!</h2>
    <p class="text-muted mb-0">Here's an overview of your library activity.</p>
  </div>

  <?php
  $total_borrowed = mysqli_num_rows(mysqli_query($connection, "SELECT * FROM transactions WHERE user_id='$uid' AND status='borrowed'"));
  $total_returned = mysqli_num_rows(mysqli_query($connection, "SELECT * FROM transactions WHERE user_id='$uid' AND status='returned'"));
  $total_overdue = mysqli_num_rows(mysqli_query($connection, "SELECT * FROM transactions WHERE user_id='$uid' AND status='borrowed' AND return_date < CURDATE()"));
  ?>
  <div class="row g-3">
    <div class="col-6 col-md-4">
      <div class="card text-center shadow-sm h-100">
        <div class="card-body">
          <div class="text-muted small mb-1">Borrowed Books</div>
          <div class="display-6 fw-bold text-warning"><?= $total_borrowed ?></div>
        </div>
      </div>
    </div>

    <div class="col-6 col-md-4">
      <div class="card text-center shadow-sm h-100">
        <div class="card-body">
          <div class="text-muted small mb-1">Returned Books</div>
          <div class="display-6 fw-bold text-success"><?= $total_returned ?></div>
        </div>
      </div>
    </div>

    <div class="col-6 col-md-4">
      <div class="card text-center shadow-sm h-100">
        <div class="card-body">
          <div class="text-muted small mb-1">Overdue Books</div>
          <div class="display-6 fw-bold text-danger"><?= $total_overdue ?></div>
        </div>
      </div>
    </div>
  </div>

  <div class="mt-5">
    <div class="d-flex justify-content-between align-items-center">
      <h4 class="mb-0">Recent Borrowed Books</h4>
      <a href="borrowed_books.php" class="btn btn-outline-primary btn-sm">
        See all <i class="bi bi-arrow-right"></i>
      </a>
    </div>

    <?php
    $recent = mysqli_query($connection, "
      SELECT t.*, b.title
      FROM transactions t
      JOIN books_db b ON t.book_id = b.book_id
      WHERE t.user_id='$uid'
      ORDER BY t.issue_date DESC
      LIMIT 5
    ");
    ?>
    <div class="table-responsive mt-3">
      <table class="table table-striped table-bordered align-middle">
        <thead class="table-light">
          <tr>
            <th style="width: 40%;">Book Title</th>
            <th style="width: 20%;">Date Borrowed</th>
            <th style="width: 20%;">Due Date</th>
            <th style="width: 20%;">Status</th>
          </tr>
        </thead>
        <tbody>
          <?php if (mysqli_num_rows($recent) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($recent)): ?>
              <?php
              $isOverdue = ($row['status'] == 'borrowed' && strtotime($row['return_date']) < time());
              ?>
              <tr>
                <td><?= htmlspecialchars($row['title']) ?></td>
                <td><?= htmlspecialchars($row['issue_date']) ?></td>
                <td><?= htmlspecialchars($row['return_date']) ?></td>
                <td>
                  <?php if ($isOverdue): ?>
                    <span class="badge bg-danger">Overdue</span>
                  <?php elseif ($row['status'] == 'borrowed'): ?>
                    <span class="badge bg-warning text-dark">Borrowed</span>
                  <?php else: ?>
                    <span class="badge bg-success">Returned</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="4" class="text-center text-muted py-3">No recent activity.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>

  <footer class="text-center py-3 mt-5">
    <small>&copy; 2025 Library Management System | Student Dashboard</small>
  </footer>

  <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
