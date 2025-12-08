<?php
session_start();
include('dbcon.php');

if (empty($_SESSION['user_id']) || (($_SESSION['role'] ?? '') !== 'admin')) {
  header('Location: admin_login.php');
  exit;
}

$uid = $_SESSION['user_id'];
$admin_name = 'Admin';

// Fetch admin name
$result = mysqli_query($connection, "SELECT name FROM users WHERE user_id='$uid' AND role='admin' LIMIT 1");
if ($row = mysqli_fetch_assoc($result)) $admin_name = $row['name'];

mysqli_query($connection, "
  UPDATE transactions
  SET status = 'overdue'
  WHERE status = 'borrowed'
    AND due_date < CURDATE()
    AND return_date IS NULL
");

// Counts
$total_books = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) AS count FROM books_db"))['count'] ?? 0;
$borrowed_books = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) AS count FROM transactions WHERE status='borrowed'"))['count'] ?? 0;
$overdue_books = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) AS count FROM transactions WHERE status='overdue'"))['count'] ?? 0;
$total_students = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) AS count FROM users WHERE role='student'"))['count'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Library | Admin Dashboard</title>

  <link rel="stylesheet" href="css/index.css">
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
  <div class="container-fluid px-4">
    <div class="d-flex align-items-center">
      <img src="img/pup_logo.png" alt="PUP Logo" style="height: 40px; width: auto; margin-right: 10px;">
      <a class="navbar-brand fw-bold mb-0" href="admin_dashboard.php">Library Admin Panel</a>
    </div>

    <div class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link active" href="admin_dashboard.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="admin_books.php">Books</a></li>
        <li class="nav-item"><a class="nav-link" href="admin_borrowed_list.php">Borrowed</a></li>
        <li class="nav-item"><a class="nav-link" href="admin_pending_list.php">Request</a></li>
        <li class="nav-item"><a class="nav-link" href="admin_rejected_list.php">Rejected</a></li>
        <li class="nav-item"><a class="nav-link" href="admin_student_list.php">Students</a></li>
      </ul>
    </div>

    <div class="dropdown ms-3">
      <button class="btn btn-outline-dark btn-sm dropdown-toggle d-flex align-items-center" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-person-circle me-1"></i> <?= htmlspecialchars($admin_name) ?>
      </button>
      <ul class="dropdown-menu dropdown-menu-end">
        <li><a class="dropdown-item" href="admin_profile.php"><i class="bi bi-person"></i> Profile</a></li>
        <li><a class="dropdown-item" href="admin_changepass.php"><i class="bi bi-gear"></i> Change Password</a></li>                
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

<!-- Main Content -->
<main class="container my-4">

  <!-- Dashboard Overview -->
  <div class="bg-light p-4 rounded-3 mb-4 shadow-sm">
    <h2 class="fw-bold mb-1">Welcome back, <?= htmlspecialchars($admin_name) ?>!</h2>
    <p class="text-muted mb-0">Here's your dashboard overview.</p>
  </div>

  <div class="row g-3">
    <div class="col-6 col-md-3">
      <div class="card text-center shadow-sm h-100">
        <div class="card-body">
          <div class="text-muted small mb-1">Total Books</div>
          <div class="display-6 fw-bold text-primary"><?= $total_books ?></div>
        </div>
        <div class="card-footer bg-transparent">
          <a href="admin_books.php" class="small text-decoration-none">Manage books <i class="bi bi-arrow-right-short"></i></a>
        </div>
      </div>
    </div>

    <div class="col-6 col-md-3">
      <div class="card text-center shadow-sm h-100">
        <div class="card-body">
          <div class="text-muted small mb-1">Borrowed Books</div>
          <div class="display-6 fw-bold text-warning"><?= $borrowed_books ?></div>
        </div>
        <div class="card-footer bg-transparent">
          <a href="admin_borrowed_list.php?status=borrowed" class="small text-decoration-none">View borrowed <i class="bi bi-arrow-right-short"></i></a>
        </div>
      </div>
    </div>

    <div class="col-6 col-md-3">
      <div class="card text-center shadow-sm h-100">
        <div class="card-body">
          <div class="text-muted small mb-1">Overdue Books</div>
          <div class="display-6 fw-bold text-danger"><?= $overdue_books ?></div>
        </div>
        <div class="card-footer bg-transparent">
          <a href="admin_borrowed_list.php?status=overdue" class="small text-decoration-none">See overdue <i class="bi bi-arrow-right-short"></i></a>
        </div>
      </div>
    </div>

    <div class="col-6 col-md-3">
      <div class="card text-center shadow-sm h-100">
        <div class="card-body">
          <div class="text-muted small mb-1">Registered Students</div>
          <div class="display-6 fw-bold text-success"><?= $total_students ?></div>
        </div>
        <div class="card-footer bg-transparent">
          <a href="admin_student_list.php" class="small text-decoration-none">Manage students <i class="bi bi-arrow-right-short"></i></a>
        </div>
      </div>
    </div>
  </div>

  <!-- Recently Transactions -->
  <div class="mt-5">
    <div class="d-flex justify-content-between align-items-center">
      <h3 class="mb-0">Recent Transactions</h3>
      <a href="transactions.php" class="btn btn-outline-primary btn-sm">
        See all <i class="bi bi-arrow-right"></i>
      </a>
    </div>

    <div class="table-responsive mt-3">
      <table class="table table-hover table-striped table-bordered align-middle">
        <thead class="table-light">
          <tr>
            <th style="width: 50;">Book Title</th>
            <th style="width: 20%;">Student</th>
            <th style="width: 15%;">Date Borrowed</th>
            <th style="width: 10%;">Due Date</th>
            <th style="width: 5%;">Status</th>
          </tr>
        </thead>
        <?php
        $query = "
          SELECT t.*, b.title, u.name AS student
          FROM transactions t
          JOIN books_db b ON t.book_id = b.book_id
          JOIN users u ON t.user_id = u.user_id
          ORDER BY t.id DESC
          LIMIT 5
        ";
        $result = mysqli_query($connection, $query);
        ?>
        <tbody>
          <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
              <tr>
                <td><?= htmlspecialchars($row['title']) ?></td>
                <td><?= htmlspecialchars($row['student']) ?></td>
                <td><?= ($row['status'] === 'pending' || $row['status'] === 'rejected') ? '—' : htmlspecialchars($row['issue_date'] ?: '—') ?></td>
                <td><?= ($row['status'] === 'pending' || $row['status'] === 'rejected') ? '—' : htmlspecialchars($row['due_date'] ?: '—') ?></td>
                <td class="text-center">
                  <?php
                    $status = strtolower($row['status']);
                    $badge = match($status) {
                      'pending'  => 'bg-secondary text-white',  
                      'borrowed' => 'bg-warning text-dark',     
                      'returned' => 'bg-success text-white',    
                      'overdue'  => 'bg-danger text-white',     
                      'rejected' => 'bg-dark text-white',      
                      default    => 'bg-light text-dark'
                    };
                  ?>
                  <span class="badge <?= $badge ?>"><?= ucfirst($status) ?></span>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="5" class="text-center text-muted py-3">No recent transactions.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</main>

<footer class="text-center py-3 mt-5">
  <small>&copy; 2025 Library Management System | Admin Dashboard</small>
</footer>

<script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
