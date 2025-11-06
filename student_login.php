<?php
session_start();
include('dbcon.php');

if (isset($_POST['btnlogin'])) {
    $id = $_POST['user'];
    $pass = $_POST['password'];

    $sql = "SELECT * FROM users WHERE user_id = '$id' AND role = 'student' LIMIT 1";
    $result = mysqli_query($connection, $sql);

    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);

        if (password_verify($pass, $row['password'])) {
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['role'] = $row['role'];
            header("Location: student_dashboard.php");
            exit();
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "Account not found.";
    }
}
?>

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'reset_success'): ?>
  <style>
    .alert-container { transition: opacity 0.6s ease; }
    .fade-out { opacity: 0; }
  </style>

  <div class="alert-container position-fixed top-0 start-50 translate-middle-x mt-3"
       style="z-index:1055;width:350px;">
    <div class="alert alert-success text-center py-2 m-0 shadow-sm">
      Password reset successfully. You may now log in.
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


<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Login | PUP Library Portal</title>

    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/landing_page.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
      .background {
        background: url('img/pup_school.JPG') center center / cover no-repeat;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100vh;
      }

      #togglePassword {
      background: none !important;
      box-shadow: none !important;
      }

      #togglePassword:hover,
      #togglePassword:focus {
        background: none !important;
        color: inherit !important;
        box-shadow: none !important;
      }
    </style>
  </head>

  <body>

    <div class="background"></div>
    <div class="right-side">
      <div class="p-4 text-center" style="width: 100%; max-width: 400px;">
        <!-- Logo -->
        <img src="img/pup_logo.png" alt="PUP Logo" class="img-fluid mb-3" style="width:90px;">
        <h4 class="fw-bold mb-3">Student Login</h4>

        <!-- Login Form -->
        <form method="post">
          <div class="mb-3">
            <input type="text" name="user" class="form-control" placeholder="Student Number" required>
          </div>
        <div class="mb-3 position-relative">
          <input type="password" id="password" name="password" class="form-control" placeholder="Password" required>
          <button type="button" id="togglePassword"
                  class="btn btn-sm btn-outline-secondary position-absolute top-50 end-0 translate-middle-y me-2"
                  style="border:none;">
            <i class="bi bi-eye"></i>
          </button>
        </div>
          <button type="submit" name="btnlogin" class="btn btn-signin w-100 py-2">Sign in</button>
        </form>

        <!-- Display error if exists -->
        <?php if (isset($error)): ?>
          <div class="alert alert-danger text-white fw-semibold mt-3 py-2 mb-0"
              style="background-color: #dc3545; border: none;">
            <?= htmlspecialchars($error) ?>
          </div>
        <?php endif; ?>
     
        <!-- Forgot Pass -->
        <div class="mt-3">
          <a href="forgot_password.php" class="text-decoration-none text-danger fw-bold fs-6">Forgot password?</a>
        </div>

        <p class="small text-muted mt-4 mb-0">
          By using this service, you understood and agree to the PUP Online Services
          <a href="https://www.pup.edu.ph/terms/" target="_blank">Terms of Use</a> and <a href="https://www.pup.edu.ph/privacy/" target="_blank">Privacy Statement</a>.
        </p>        
      </div>
    </div>

    <script src="js/bootstrap.bundle.min.js"></script>
    <script>
    const toggle = document.getElementById('togglePassword');
    const input = document.getElementById('password');

    toggle.addEventListener('click', () => {
      const isPassword = input.type === 'password';
      input.type = isPassword ? 'text' : 'password';
      toggle.innerHTML = isPassword ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
    });
    </script>
  </body>
</html>
