<?php
session_start();
include('dbcon.php');

if (empty($_SESSION['user_id']) || (($_SESSION['role'] ?? '') !== 'admin')) {
  header('Location: admin_login.php');
  exit;
}

$uid = $_SESSION['user_id'];
$message = "";

// Fetch admin data
$query = mysqli_query($connection, "SELECT * FROM users WHERE user_id='$uid' AND role='admin' LIMIT 1");
$admin = mysqli_fetch_assoc($query);

// Update email if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = mysqli_real_escape_string($connection, $_POST['email']);

  if (!empty($email)) {
    $update = mysqli_query($connection, "UPDATE users SET email='$email' WHERE user_id='$uid' AND role='admin'");
    if ($update) {
      $message = "Profile updated successfully.";
      // Refresh data after update
      $query = mysqli_query($connection, "SELECT * FROM users WHERE user_id='$uid' AND role='admin' LIMIT 1");
      $admin = mysqli_fetch_assoc($query);
    } else {
      $message = "Failed to update profile. Please try again.";
    }
  }
}

$admin_name = $admin['name'] ?? 'Admin';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Library | Admin Profile</title>

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


<main>
  <div class="container d-flex justify-content-center align-items-center" style="min-height: 60vh;">
    <div class="col-md-9">
      <h2 class="fw-bold mb-3">Admin Profile</h2>
      <div class="card shadow-sm">
        <div class="card-header bg-white fw-bold">
          <h5 class="text-danger fw-bold mb-0">
            <?= htmlspecialchars($admin['name'] ?? 'Admin'); ?> (<?= htmlspecialchars($admin['user_id'] ?? 'N/A'); ?>)
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
                  <td class="fw-semibold">User ID</td>
                  <td><?= htmlspecialchars($admin['user_id'] ?? 'N/A'); ?></td>
                  <td><i class="bi bi-person-badge text-danger"></i></td>
                </tr>
                <tr>
                  <td class="fw-semibold">Name</td>
                  <td><?= htmlspecialchars($admin['name'] ?? 'N/A'); ?></td>
                  <td><i class="bi bi-person text-danger"></i></td>
                </tr>
                <tr>
                  <td class="fw-semibold">Email</td>
                  <td>
                    <input type="email" name="email" class="form-control"
                      value="<?= htmlspecialchars($admin['email'] ?? ''); ?>" required>
                  </td>
                  <td><i class="bi bi-envelope text-danger"></i></td>
                </tr>
                <tr>
                  <td class="fw-semibold">Role</td>
                  <td><?= htmlspecialchars(ucfirst($admin['role'] ?? 'Admin')); ?></td>
                  <td><i class="bi bi-shield-lock text-danger"></i></td>
                </tr>
              </tbody>
            </table>

            <p class="mt-3 small text-muted">
              I hereby certify that all the information provided are true and correct to the best of my knowledge.
            </p>
            <button type="submit" class="btn btn-success btn-sm">Save Changes</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</main>


<footer class="text-center py-3 mt-5">
  <small>&copy; 2025 Library Management System | Admin Dashboard</small>
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


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
