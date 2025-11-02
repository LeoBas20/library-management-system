<?php
session_start();
include('dbcon.php');


if (empty($_SESSION['user_id']) || (($_SESSION['role'] ?? '') !== 'student')) {
  header('Location: student_login.php');
  exit;
}

$uid = $_SESSION['user_id'];


$query = "SELECT user_id, name, email, role FROM users WHERE user_id = ? AND role = 'student' LIMIT 1";
$stmt = $connection->prepare($query);
$stmt->bind_param("s", $uid);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $new_email = trim($_POST['email']);
  if (!empty($new_email)) {
    $update = $connection->prepare("UPDATE users SET email=? WHERE user_id=?");
    $update->bind_param("ss", $new_email, $uid);
    $update->execute();
    $user['email'] = $new_email; // reflect update instantly
    $message = "Email updated successfully!";
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Student Profile</title>

  <link rel="stylesheet" href="css/index.css">
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <div class="container-fluid px-4">
    <div class="d-flex align-items-center">
      <img src="img/pup_logo.png" alt="PUP Logo" style="height:40px;width:auto;margin-right:10px;">
      <a class="navbar-brand fw-bold mb-0" href="student_dashboard.php">Student Dashboard</a>
    </div>

    <div class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="student_dashboard.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="student_books.php">Books</a></li>
        <li class="nav-item"><a class="nav-link" href="student_borrowed_books.php">Borrowed</a></li>
      </ul>
    </div>

    <!-- Right: Dropdown -->
    <div class="dropdown ms-3">
      <button class="btn btn-outline-dark btn-sm dropdown-toggle d-flex align-items-center" 
              type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-person-circle"></i>
      </button>
      <ul class="dropdown-menu dropdown-menu-end">
        <li><a class="dropdown-item" href="student_profile.php"><i class="bi bi-person"></i> Profile</a></li>
        <li><a class="dropdown-item" href="student_changepass.php"><i class="bi bi-gear"></i> Change Password</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
      </ul>
    </div>
  </div>
</nav>


 <!-- Main Content -->
<main>
  <div class="container d-flex justify-content-center align-items-center mt-5">
    <div class="col-md-9">
      <h2 class="fw-bold mb-3">Personal Data</h2>
      <div class="card shadow-sm">
        <div class="card-header bg-white fw-bold">
            <h5 class="fw-bold mb-0">
            <?= htmlspecialchars($user['name'] ?? 'Student'); ?> (<?= htmlspecialchars($user['user_id'] ?? 'N/A'); ?>)
            </h5>
        </div>
        <div class="card-body">
          <?php if (!empty($message)): ?>
            <div class="alert alert-success py-2"><?= htmlspecialchars($message) ?></div>
          <?php endif; ?>

          <form method="POST">
            <table class="table table-hover">
              <tbody>
                <tr>
                  <td>Student Number</td>
                  <td><?= htmlspecialchars($user['user_id'] ?? 'N/A'); ?></td>
                  <td><i class="bi bi-person-badge text-danger"></i></td>
                </tr>
                <tr>
                  <td>Name</td>
                  <td><?= htmlspecialchars($user['name'] ?? 'N/A'); ?></td>
                  <td><i class="bi bi-person text-danger"></i></td>
                </tr>
                <tr>
                  <td>Email</td>
                  <td>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email'] ?? ''); ?>" required>
                  </td>
                  <td><i class="bi bi-envelope text-danger"></i></td>
                </tr>
              </tbody>
            </table>
            <p class="mt-3">I hereby certify that all the information provided are true and correct to the best of my knowledge.</p>
            <button type="submit" class="btn btn-success">Save</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</main>


  <footer class="text-center py-3 mt-5">
    <small>&copy; 2025 Library Management System | Student Dashboard</small>
  </footer>

<script>
document.addEventListener("DOMContentLoaded", function () {
  const emailInput = document.querySelector('input[name="email"]');
  const saveBtn = document.querySelector('button[type="submit"]');
  const originalEmail = emailInput.value.trim();

  saveBtn.disabled = true;

  emailInput.addEventListener("input", function () {
    if (emailInput.value.trim() !== originalEmail && emailInput.value.trim() !== "") {
      saveBtn.disabled = false;
    } else {
      saveBtn.disabled = true;
    }
  });
});
</script>
<script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
