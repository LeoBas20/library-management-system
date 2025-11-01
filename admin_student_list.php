<?php
session_start();
include('dbcon.php');

if (empty($_SESSION['user_id']) || (($_SESSION['role'] ?? '') !== 'admin')) {
  header('Location: admin_login.php');
  exit;
}

$uid = $_SESSION['user_id'];
$admin_name = 'Admin';

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
  <title>Library | Student List</title>

  <link rel="stylesheet" href="css/index.css">
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

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
        <li class="nav-item"><a class="nav-link" href="admin_borrowed_list.php">Borrowed</a></li>
        <li class="nav-item"><a class="nav-link" href="admin_overdue_list.php">Overdue</a></li>
        <li class="nav-item"><a class="nav-link active" href="admin_manage_students.php">Students</a></li>
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


<main class="container">
  <div class="mt-5">
    <div class="d-flex justify-content-between align-items-center">
      <h2 class="mb-0">Students</h2>

      <!-- Search Form -->
      <form method="GET" class="d-flex" role="search">
        <input type="text" name="search" class="form-control me-2" style="width:500px;"
          placeholder="Search for..."
          value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-search me-1"></i> Search
        </button>
      </form>
    </div>

    <div class="table-responsive mt-3">
      <table class="table table-striped table-bordered align-middle table-fixed" style="width:100%;">
        <thead>
        <tr>
        <th style="width:45%; white-space:nowrap;">Student Name</th>
        <th style="width:30%; white-space:nowrap;">Student ID</th>
        <th style="width:25%; white-space:nowrap;">Email</th>
        </thead>
        <tbody>
          <?php
          $search = trim($_GET['search'] ?? '');
          $safe = mysqli_real_escape_string($connection, $search);

          if ($search === '') {
            $sql = "SELECT user_id, name, email 
                    FROM users 
                    WHERE role='student' 
                    ORDER BY name";
          } else {
            $sql = "SELECT user_id, name, email 
                    FROM users 
                    WHERE role='student' 
                    AND (name LIKE '%$safe%' 
                    OR user_id LIKE '%$safe%' 
                    OR email LIKE '%$safe%') 
                    ORDER BY name";
          }

          $result = mysqli_query($connection, $sql);

          if (mysqli_num_rows($result)) {
            foreach ($result as $row) {
              echo "<tr>
                      <td>" . htmlspecialchars($row['name']) . "</td>
                      <td>" . htmlspecialchars($row['user_id']) . "</td>
                      <td>" . htmlspecialchars($row['email']) . "</td>
                    </tr>";
            }
          } else {
            echo "<tr><td colspan='3' class='text-center text-danger'>No students found</td></tr>";
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>
</main>

<footer class="text-center py-3 mt-5">
  <small>&copy; 2025 Library Management System | Admin Dashboard</small>
</footer>

<script src="js/bootstrap.bundle.min.js"></script>
<script>
const searchBox = document.querySelector('input[name="search"]');
searchBox.addEventListener('input', () => {
  if (searchBox.value === '') window.location = 'admin_student_list.php';
});
</script>
</body>
</html>
