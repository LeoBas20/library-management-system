<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PUP Library Portal</title>


    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/landing_page.css">
    <style>
    .left-side {
      background: url("img/pup_school.JPG") center center / cover no-repeat;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100vh;
    }
    </style>
  </head>

  <body>

    <div class="left-side"></div>


    <div class="right-side">
      <div class="text-center p-4">

        <img src="img/pup_logo.png" alt="PUP Logo" class="img-fluid mb-3" style="width:90px;">

        <h3 class="fw-bold">Hi, PUPian!</h3>
        <p class="text-muted mb-4">Please click or tap your destination.</p>

        <div class="d-grid gap-2">
          <a href="student_login.php" class="btn btn-primary btn-lg">Student</a>
          <a href="admin_login.php" class="btn btn-danger btn-lg">Librarian</a>
        </div>

        <p class="small text-muted mt-4 mb-0">
          By using this service, you understood and agree to the PUP Online Services
          <a href="https://www.pup.edu.ph/terms/">Terms of Use</a> and <a href="https://www.pup.edu.ph/privacy/">Privacy Statement</a>.
        </p>
      </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="js/bootstrap.bundle.min.js"></script>
  </body>
</html>
