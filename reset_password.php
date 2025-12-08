<?php
include('dbcon.php');

$token = $_GET["token"] ?? null;
if (!$token) {
    die("Token missing.");
}

$token_hash = hash("sha256", $token);

$sql = "SELECT * FROM users WHERE reset_token_hash = ?";

$stmt = $connection->prepare($sql); 
$stmt->bind_param("s", $token_hash);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("Token not found.");
}

if (strtotime($user["reset_token_expires_at"]) <= time()) {
    die("Token has expired.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reset Password</title>

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

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
    <div class="container-fluid px-4">
      <div class="d-flex align-items-center">
        <img src="img/pup_logo.png" alt="PUP Logo" class="me-2" style="height:40px;width:auto;">
        <h5 class="navbar-brand fw-bold mb-0">Reset Password</h5>
      </div>
    </div>
  </nav>

  <main class="container mt-5" style="max-width: 1000px;">
    <h2 class="fw-bold mb-3">Create New Password</h2>
    <div class="card shadow-sm">

      <form method="POST" action="process_reset_password.php" autocomplete="off" id="changePassForm">
        <div class="card-body">

          <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

          <!-- New Password -->
          <div class="mb-3">
            <div class="input-group" style="max-width: 400px;">
              <span class="input-group-text"><i class="bi bi-key"></i></span>
              <input type="password" name="password" id="password" class="form-control" placeholder="New Password" minlength="6" required>
              <button type="button"
                      class="btn btn-outline-secondary toggle-password"
                      style="border-color: var(--bs-border-color);"
                      data-target="password">
                <i class="bi bi-eye"></i>
              </button>
            </div>
            <div class="form-text">Use at least 6 characters.</div>
          </div>

          <!-- Confirm Password -->
          <div class="mb-2">
            <div class="input-group" style="max-width: 400px;">
              <span class="input-group-text"><i class="bi bi-key"></i></span>
              <input type="password" name="password_confirm" id="password_confirm" class="form-control" placeholder="Confirm Password" minlength="6" required>
              <button type="button"
                      class="btn btn-outline-secondary toggle-password"
                      style="border-color: var(--bs-border-color);"
                      data-target="password_confirm">
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

  <!-- Footer -->
  <footer class="text-center py-3 mt-auto">
    <small>&copy; 2025 Library Management System</small>
  </footer>

  <script src="js/bootstrap.bundle.min.js"></script>

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

    // Match validation
    (function () {
      const pw = document.getElementById('password');
      const confirmPw = document.getElementById('password_confirm');
      const btn = document.getElementById('submitBtn');
      const hint = document.getElementById('matchHint');

      function validate() {
        const short = pw.value.length > 0 && pw.value.length < 6;
        const match = pw.value && pw.value === confirmPw.value;

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

      pw.addEventListener('input', validate);
      confirmPw.addEventListener('input', validate);
      validate();
    })();
  </script>

</body>
</html>

