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
if ($row = mysqli_fetch_assoc($result)) {
  $admin_name = $row['name'];
}

// Automatically update overdue books
mysqli_query($connection, "
  UPDATE transactions
  SET status = 'overdue'
  WHERE status = 'borrowed'
    AND return_date < CURDATE()
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Library | All Transactions</title>

  <link rel="stylesheet" href="css/index.css">
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="css/datatables.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <style>
    .container { margin-top: 50px; }
    table.dataTable thead th { white-space: nowrap; }
  </style>
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
        <li class="nav-item"><a class="nav-link" href="admin_dashboard.php">Home</a></li>
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
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="fw-bold mb-0">All Transactions</h2>
  </div>

  <div class="table-responsive">
    <table id="myTable" class="table table-striped table-bordered align-middle w-100">
      <thead>
        <tr>
          <th style="width:35%;">Book Title</th>
          <th style="width:20%;">Student</th>
          <th style="width:12%;">Request Date</th>
          <th style="width:12%;">Date Borrowed</th>
          <th style="width:12%;">Due Date</th>
          <th style="width:9%;">Status</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $query = "
          SELECT 
            t.*, b.title, u.name AS student
          FROM transactions t
          JOIN books_db b ON t.book_id = b.book_id
          JOIN users u ON t.user_id = u.user_id
          ORDER BY t.id DESC
        ";

        $result = mysqli_query($connection, $query);

        if (mysqli_num_rows($result) > 0) {
          while ($row = mysqli_fetch_assoc($result)) {
            $status = strtolower($row['status']);
            $badge = match($status) {
              'pending'  => 'bg-secondary text-white',
              'borrowed' => 'bg-warning text-dark',
              'returned' => 'bg-success text-white',
              'overdue'  => 'bg-danger text-white',
              'rejected' => 'bg-dark text-white',
              default    => 'bg-light text-dark'
            };
            echo "<tr>
                    <td>" . htmlspecialchars($row['title']) . "</td>
                    <td>" . htmlspecialchars($row['student']) . "</td>
                    <td>" . htmlspecialchars($row['request_date']) . "</td>
                    <td>" . htmlspecialchars($row['issue_date'] ?: '—') . "</td>
                    <td>" . htmlspecialchars($row['return_date'] ?: '—') . "</td>
                    <td><span class='badge $badge'>" . ucfirst($status) . "</span></td>
                  </tr>";
          }
        } else {
          echo "<tr><td colspan='6' class='text-center text-muted py-3'>No transactions found.</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </div>
</main>

<footer class="text-center py-3 mt-5">
  <small>&copy; 2025 Library Management System | Admin Dashboard</small>
</footer>

<script src="js/bootstrap.bundle.min.js"></script>
<script src="js/datatables.min.js"></script>
<script>
$(document).ready(function() {
  $('#myTable').DataTable({
    order: [[2, 'desc']], // Sort by request date
    pageLength: 10,
    language: {
      emptyTable: "No transactions found."
    }
  });
});
</script>

</body>
</html>
