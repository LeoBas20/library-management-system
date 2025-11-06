<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Forgot Password | PUP Library Portal</title>

  <link rel="stylesheet" href="css/index.css">
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>

<body class="bg-light d-flex flex-column min-vh-100">

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
    <div class="container-fluid px-4">
      <div class="d-flex align-items-center">
        <a href="landing_page.php">
          <img src="img/pup_logo.png" alt="PUP Logo" class="me-2" style="height:40px;width:auto;">
        </a>
        <h5 class="navbar-brand fw-bold mb-0">Forgot Password</h5>
      </div>
    </div>
  </nav>

  <?php if (isset($_GET['msg'])): ?>
    <?php
      $messages = [
        'sent'   => ['Reset link sent. Please check your inbox.', 'success'],
        'failed' => ['Failed to send email. Please try again later.', 'danger']
      ];
      [$text, $type] = $messages[$_GET['msg']] ?? [null, null];
    ?>
    <?php if ($text): ?>
      <style>
        .alert-container { transition: opacity 0.6s ease; }
        .fade-out { opacity: 0; }
      </style>

      <div class="alert-container position-fixed top-0 start-50 translate-middle-x mt-3" 
          style="z-index:1055;width:350px;">
        <div class="alert alert-<?= $type ?> text-center py-2 m-0 shadow-sm">
          <?= htmlspecialchars($text) ?>
        </div>
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
        }, 2500);
      </script>
    <?php endif; ?>
  <?php endif; ?>

  <!-- Main Card -->
  <main class="flex-fill d-flex justify-content-center align-items-start mt-5">
    <div class="card border-0 shadow-sm p-4 text-center" style="max-width:380px;">
      <p class="text-muted mb-3">
        You forgot your password?<br>
        You can easily request a new password here.
      </p>

      <form method="post" action="send_password_reset.php" class="text-start">
        <div class="input-group mb-3">
          <input type="email" name="email" class="form-control" placeholder="Email Address" required>
          <span class="input-group-text bg-white border-start-0">
            <i class="bi bi-envelope"></i>
          </span>
        </div>
        <button type="submit" class="btn w-100 py-2 text-white fw-semibold" style="background-color:#800000;">
          Request new password
        </button>
      </form>

      <div class="mt-3">
        <a href="student_login.php" class="text-decoration-none fw-semibold" style="color:#800000;">
          Sign in
        </a>
      </div>
    </div>
  </main>

  <!-- Footer -->
  <footer class="text-center py-3 mt-auto">
    <small>&copy; 2025 Library Management System</small>
  </footer>

  <script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
