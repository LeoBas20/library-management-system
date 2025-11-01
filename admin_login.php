<?php
session_start();
include('dbcon.php');

if (isset($_POST['btnlogin'])) {
    $id = $_POST['user'];
    $pass = $_POST['password'];

    $sql = "SELECT * FROM users WHERE user_id = '$id' AND role = 'admin' LIMIT 1";
    $result = mysqli_query($connection, $sql);

    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);

        if ($pass == $row['password']) {
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['role'] = $row['role'];

            header("Location: admin_dashboard.php");
            exit();
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "Account not found.";
    }
}
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login | PUP Library Portal</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/landing_page.css">

    <style>
      /* Full background image (left side) */
      .left-side {
        background: url('img/pup_school.JPG') center center / cover no-repeat;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100vh;
      }
    </style>
  </head>

  <body>
    <!-- LEFT BACKGROUND IMAGE -->
    <div class="left-side"></div>

    <!-- RIGHT SIDE LOGIN FORM -->
    <div class="right-side">
      <div class="p-4 text-center" style="width: 100%; max-width: 400px;">
        <!-- Logo -->
        <img src="img/pup_logo.png" alt="PUP Logo" class="img-fluid mb-3" style="width:90px;">
        <h4 class="fw-bold mb-3">Admin Login</h4>

        <!-- Login Form -->
        <form method="post">
          <div class="mb-3">
            <input type="text" name="user" class="form-control" placeholder="User ID" required>
          </div>
          <div class="mb-3">
            <input type="password" name="password" class="form-control" placeholder="Password" required>
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
     
        <p class="forgot text-danger mt-3 mb-0">Forgot password?</p>

        <p class="small text-muted mt-4 mb-0">
          By using this service, you understood and agree to the PUP Online Services
          <a href="https://www.pup.edu.ph/terms/">Terms of Use</a> and <a href="https://www.pup.edu.ph/privacy/">Privacy Statement</a>.
        </p>        
      </div>
    </div>

    <script src="js/bootstrap.bundle.min.js"></script>
  </body>
</html>
