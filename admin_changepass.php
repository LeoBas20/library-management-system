<?php
session_start();
include('dbcon.php');

if (empty($_SESSION['user_id']) || (($_SESSION['role'] ?? '') !== 'admin')) {
  header('Location: admin_login.php');
  exit;
}

$uid = $_SESSION['user_id'];
$message = "";

/* Fetch admin data */
$query  = mysqli_query($connection, "SELECT * FROM users WHERE user_id='$uid' AND role='admin' LIMIT 1");
$admin  = mysqli_fetch_assoc($query);
$admin_name = $admin['name'] ?? 'Admin';
$admin_display = "{$admin_name} ({$admin['user_id']})";

/* Handle password change (hashed version) */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $old = $_POST['old_password'] ?? '';
  $new = $_POST['new_password'] ?? '';
  $conf = $_POST['confirm_password'] ?? '';

  if ($new !== $conf) {
    header('Location: admin_changepass.php?msg=nomatch');
    exit;
  }

  $res = mysqli_query($connection, "SELECT password FROM users WHERE user_id='$uid' AND role='admin' LIMIT 1");
  $row = mysqli_fetch_assoc($res);

  if (!$row || !password_verify($old, $row['password'])) {
    header('Location: admin_changepass.php?msg=incorrect');
    exit;
  }

  $hashed = password_hash($new, PASSWORD_DEFAULT);
  $update = mysqli_query($connection, "UPDATE users SET password='$hashed' WHERE user_id='$uid' AND role='admin'");

  header('Location: admin_changepass.php?msg=' . ($update ? 'updated' : 'failed'));
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Library | Admin Change Password</title>

  <link rel="stylesheet" href="css/index.css">
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <style>
  .toggle-password.btn-outline-secondary:hover {
  background-color: transparent;
  color: inherit;
  box-shadow: none;
  }
  </style>
</head>

<body>

<?php if (isset($_GET['msg'])): ?>
  <?php
    $messages = [
      'updated'   => ['Password changed successfully.', 'success'],
      'incorrect' => ['Old password is incorrect.', 'danger'],
      'nomatch'   => ['Passwords do not match.', 'warning'],
      'failed'    => ['Failed to update password.', 'danger']
    ];
    [$text, $type] = $messages[$_GET['msg']] ?? [null, null];
  ?>
  <?php if ($text): ?>
    <style>
      .alert-container { transition: opacity 0.6s ease; }
      .fade-out { opacity: 0; }
    </style>
    <div class="alert-container position-fixed top-0 start-50 translate-middle-x mt-3" style="z-index:1055;width:350px;">
      <div class="alert alert-<?= $type ?> text-center py-2 m-0 shadow-sm"><?= $text ?></div>
    </div>
    <script>
      setTimeout(() => {
        const alertBox = document.querySelector('.alert-container');
        if (alertBox) {
          alertBox.classList.add('fade-out');
          setTimeout(() => alertBox.remove(), 600);
        }
        const url = new URL(window.location);
        url.searchParams.delete('msg');
        window.history.replaceState({}, '', url);
      }, 1500);
    </script>
  <?php endif; ?>
<?php endif; ?>

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

<main class="container mt-5" style="max-width: 1000px;">
  <h2 class="fw-bold mb-3">Change Password</h2>
  <div class="card shadow-sm">
    <div class="card-header">
      <h5 class="fw-bold mb-0"><?= htmlspecialchars($admin_display) ?></h5>
    </div>

    <form method="POST" action="admin_changepass.php" autocomplete="off" id="changePassForm">
      <div class="card-body">

        <!-- Old Password -->
        <div class="mb-3">
          <div class="input-group" style="max-width: 400px;">
            <span class="input-group-text"><i class="bi bi-key"></i></span>
            <input type="password" name="old_password" id="old_password" class="form-control" placeholder="Old Password" required>
            <button type="button"
                    class="btn btn-outline-secondary toggle-password"
                    style="border-color: var(--bs-border-color);"
                    data-target="old_password">
              <i class="bi bi-eye"></i>
            </button>
          </div>
        </div>

        <!-- New Password -->
        <div class="mb-3">
          <div class="input-group" style="max-width: 400px;">
            <span class="input-group-text"><i class="bi bi-key"></i></span>
            <input type="password" name="new_password" id="new_password" class="form-control" placeholder="New Password" minlength="6" required>
            <button type="button"
                    class="btn btn-outline-secondary toggle-password"
                    style="border-color: var(--bs-border-color);"
                    data-target="new_password">
              <i class="bi bi-eye"></i>
            </button>
          </div>
          <div class="form-text">Use at least 6 characters.</div>
        </div>

        <!-- Confirm Password -->
        <div class="mb-2">
          <div class="input-group" style="max-width: 400px;">
            <span class="input-group-text"><i class="bi bi-key"></i></span>
            <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Confirm Password" minlength="6" required>
            <button type="button"
                    class="btn btn-outline-secondary toggle-password"
                    style="border-color: var(--bs-border-color);"
                    data-target="confirm_password">
              <i class="bi bi-eye"></i>
            </button>
          </div>
          <div id="matchHint" class="form-text"></div>
        </div>

      </div>

      <div class="card-footer bg-light d-flex justify-content-between align-items-center">
        <button type="submit" class="btn btn-danger" id="submitBtn">Change Password</button>
      </div>
    </form>
  </div>
</main>

<script>
  // Show/hide password toggle
  document.querySelectorAll('.toggle-password').forEach(btn => {
    btn.addEventListener('click', () => {
      const target = document.getElementById(btn.dataset.target);
      const icon = btn.querySelector('i');
      const isHidden = target.type === 'password';
      target.type = isHidden ? 'text' : 'password';
      icon.className = isHidden ? 'bi bi-eye-slash' : 'bi bi-eye';
    });
  });


  // Enable button only when new/confirm match and fields are filled
  (function () {
    const newPw = document.getElementById('new_password');
    const confirmPw = document.getElementById('confirm_password');
    const btn = document.getElementById('submitBtn');
    const hint = document.getElementById('matchHint');

    function validate() {
      const short = newPw.value.length > 0 && newPw.value.length < 6;
      const match = newPw.value && newPw.value === confirmPw.value;

      if (short) {
        hint.textContent = 'Password must be at least 6 characters.';
        hint.className = 'form-text text-danger';
      } else if (match) {
        hint.textContent = 'Passwords match.';
        hint.className = 'form-text text-success';
      } else if (confirmPw.value) {
        hint.textContent = 'Passwords do not match.';
        hint.className = 'form-text text-danger';
      } else {
        hint.textContent = '';
        hint.className = 'form-text';
      }

      btn.disabled = short || !match;
    }

    newPw.addEventListener('input', validate);
    confirmPw.addEventListener('input', validate);
    validate();
  })();
</script>

<footer class="text-center py-3 mt-5">
  <small>&copy; 2025 Library Management System | Admin Dashboard</small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
