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
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Library | Borrowed Books</title>

  <link rel="stylesheet" href="css/index.css">
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

  <style>
    /* Allow long titles to wrap and avoid horizontal scroll */
    .table td, .table th {
      white-space: normal !important;
      word-wrap: break-word;
    }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
  <div class="container-fluid px-4">
    <div class="d-flex align-items-center">
      <img src="img/pup_logo.png" alt="PUP Logo" style="height:40px;width:auto;margin-right:10px;">
      <a class="navbar-brand fw-bold mb-0" href="admin_dashboard.php">Library Admin Panel</a>
    </div>

    <div class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="admin_dashboard.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="admin_books.php">Books</a></li>
        <li class="nav-item"><a class="nav-link active" href="admin_borrowed_list.php">Borrowed</a></li>
        <li class="nav-item"><a class="nav-link" href="admin_overdue_list.php">Overdue</a></li>
        <li class="nav-item"><a class="nav-link" href="admin_student_list.php">Students</a></li>
      </ul>
    </div>

    <div class="dropdown ms-3">
      <button class="btn btn-outline-dark btn-sm dropdown-toggle d-flex align-items-center" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-person-circle me-1"></i> <?= htmlspecialchars($admin_name) ?>
      </button>
      <ul class="dropdown-menu dropdown-menu-end">
        <li><a class="dropdown-item" href="admin_profile.php"><i class="bi bi-person"></i> Profile</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

<!-- Main Content -->
<main class="container my-5">
  <div>
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2 class="fw-bold mb-0">Borrowed Books</h2>

      <form method="GET" class="d-flex" role="search">
        <input type="text" name="search" class="form-control me-2" style="width:500px;"
          placeholder="Search borrowed books..."
          value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-search me-1"></i> Search
        </button>
      </form>
    </div>

    <table class="table table-striped table-bordered align-middle">
      <thead">
        <tr>
          <th style="width: 45%;">Book Title</th>
          <th style="width: 20%;">Student</th>
          <th style="width: 15%;">Date Borrowed</th>
          <th style="width: 15%;">Due Date</th>
          <th style="width: 5%;">Status</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $search = mysqli_real_escape_string($connection, $_GET['search'] ?? '');
        $query = "
          SELECT b.title, u.name AS student, t.issue_date, t.return_date, t.status
          FROM transactions t
          INNER JOIN books_db b ON t.book_id = b.book_id
          INNER JOIN users u ON t.user_id = u.user_id
          WHERE t.status = 'borrowed'
        ";

        if (!empty($search)) {
          $query .= " AND (b.title LIKE '%$search%' OR u.name LIKE '%$search%')";
        }

        $query .= " ORDER BY t.issue_date DESC";
        $result = mysqli_query($connection, $query);

        if (mysqli_num_rows($result) > 0) {
          while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['title']) . "</td>";
            echo "<td>" . htmlspecialchars($row['student']) . "</td>";
            echo "<td>" . htmlspecialchars($row['issue_date']) . "</td>";
            echo "<td>" . htmlspecialchars($row['return_date']) . "</td>";
            echo "<td><span class='badge bg-warning text-dark'>" . htmlspecialchars(ucfirst($row['status'])) . "</span></td>";
            echo "</tr>";
          }
        } else {
          echo "<tr><td colspan='5' class='text-center text-danger py-3'>No borrowed books found</td></tr>";
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
<script>
  const searchBox = document.querySelector('input[name="search"]');
  searchBox.addEventListener('input', () => {
    if (searchBox.value === '') window.location = 'admin_borrowed_list.php';
  });
</script>

</body>
</html>
