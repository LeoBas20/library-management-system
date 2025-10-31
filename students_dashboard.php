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
  <title>Library | Student Dashboard</title>
  <link rel="stylesheet" href="css/datatables.min.css">
  <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/index.css">

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
  <main>
    <div class="container mt-4">
      <h3 class="fw-bold">Home</h3>
    <h5 class="text-danger fw-bold">
      <?= htmlspecialchars($stud_name) ?> (<?= htmlspecialchars($user_id) ?>)
    </h5>




      <div class="card mb-4 p-3">
        <div class="d-flex align-items-center">
          <div class="me-3">
            <img src="https://cdn-icons-png.flaticon.com/512/337/337946.png" width="40" alt="">
          </div>
          <div>
            <a href="#" class="fw-bold text-danger text-decoration-none">Library Borrowing Policy (PDF)</a>
          </div>
        </div>
      </div>

      <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3">
          <div class="card shadow-sm sidebar">
            <div class="list-group list-group-flush">
              <a href="#" class="list-group-item"><i class="bi bi-inbox me-2"></i>Inbox (3)</a>
              <a href="#" class="list-group-item"><i class="bi bi-bookmark me-2"></i>Borrowed Books</a>
              <a href="#" class="list-group-item"><i class="bi bi-chat-left-text me-2"></i>Submit Feedback</a>
            </div>
          </div>
        </div>

        <!-- Inbox Section -->
        <div class="col-md-9">
          <div class="card shadow-sm">
            <div class="card-header bg-white fw-bold">Inbox</div>
            <div class="card-body">
              <table class="table table-hover">
                <tbody>
                  <tr>
                    <td>October 20, 2025</td>
                    <td>New Library Book Arrival: “Programming PHP, 2nd Edition”</td>
                    <td><i class="bi bi-envelope text-danger"></i></td>
                  </tr>
                  <tr>
                    <td>October 15, 2025</td>
                    <td>Reminder: Return of Borrowed Books</td>
                    <td><i class="bi bi-envelope text-danger"></i></td>
                  </tr>
                  <tr>
                    <td>October 1, 2025</td>
                    <td>Library Schedule Adjustment</td>
                    <td><i class="bi bi-envelope text-danger"></i></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <footer class="text-center py-3 mt-5">
    <small>&copy; 2025 Library Management System | Student Dashboard</small>
  </footer>

  <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
