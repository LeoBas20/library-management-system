<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Library | Admin Dashboard</title>

  <!-- Vendor CSS -->
  <link rel="stylesheet" href="css/datatables.min.css">
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <!-- Bootstrap Icons (fixed) -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

  <!-- Your site CSS -->
  <link rel="stylesheet" href="css/index.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
  <div class="container-fluid px-4">
    <div class="d-flex align-items-center">
      <img src="img/pup_logo.png" alt="PUP Logo" style="height: 40px; width: auto; margin-right: 10px;">
      <a class="navbar-brand fw-bold mb-0" href="#">Library Admin Panel</a>
    </div>

    <div class="dropdown ms-auto">
      <button class="btn btn-outline-dark btn-sm dropdown-toggle d-flex align-items-center" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-person-circle me-1"></i> Admin
      </button>
      <ul class="dropdown-menu dropdown-menu-end">
        <li>
          <a class="dropdown-item" href="profile.php">
            <i class="bi bi-person"></i> Profile
          </a>
        </li>
        <li>
          <a class="dropdown-item" href="settings.php">
            <i class="bi bi-gear"></i> Settings
          </a>
        </li>
        <li><hr class="dropdown-divider"></li>
        <li>
          <a class="dropdown-item text-danger" href="logout.php">
            <i class="bi bi-box-arrow-right"></i> Logout
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>


  <!-- Main Content -->
  <main class="container my-4">

    <!-- Dashboard Overview -->
    <h2 class="mb-4">Dashboard Overview</h2>
    <div class="row g-3">
      <!-- Tip: Replace hardcoded numbers with PHP counts from your DB -->
      <div class="col-6 col-md-3">
        <div class="card text-center shadow-sm h-100">
          <div class="card-body">
            <div class="text-muted small mb-1">Total Books</div>
            <div class="display-6 fw-bold text-primary">10</div>
          </div>
          <div class="card-footer bg-transparent">
            <a href="books.php" class="small text-decoration-none">Manage books <i class="bi bi-arrow-right-short"></i></a>
          </div>
        </div>
      </div>

      <div class="col-6 col-md-3">
        <div class="card text-center shadow-sm h-100">
          <div class="card-body">
            <div class="text-muted small mb-1">Borrowed Books</div>
            <div class="display-6 fw-bold text-warning">300</div>
          </div>
          <div class="card-footer bg-transparent">
            <a href="borrowed_list.php" class="small text-decoration-none">View borrowed <i class="bi bi-arrow-right-short"></i></a>
          </div>
        </div>
      </div>

      <div class="col-6 col-md-3">
        <div class="card text-center shadow-sm h-100">
          <div class="card-body">
            <div class="text-muted small mb-1">Overdue Books</div>
            <div class="display-6 fw-bold text-danger">25</div>
          </div>
          <div class="card-footer bg-transparent">
            <a href="borrowed_list.php?filter=overdue" class="small text-decoration-none">See overdue <i class="bi bi-arrow-right-short"></i></a>
          </div>
        </div>
      </div>

      <div class="col-6 col-md-3">
        <div class="card text-center shadow-sm h-100">
          <div class="card-body">
            <div class="text-muted small mb-1">Registered Students</div>
            <div class="display-6 fw-bold text-success">150</div>
          </div>
          <div class="card-footer bg-transparent">
            <a href="students.php" class="small text-decoration-none">Manage students <i class="bi bi-arrow-right-short"></i></a>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="mt-5">
      <h3 class="mb-3">Quick Actions</h3>
      <div class="d-flex flex-wrap gap-3">
        <a href="add_book.php" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Add New Book</a>
        <a href="manage_students.php" class="btn btn-secondary"><i class="bi bi-people"></i> Manage Students</a>
        <a href="borrowed_list.php" class="btn btn-warning text-dark"><i class="bi bi-journal"></i> View Borrowed</a>
        <a href="reports.php" class="btn btn-success"><i class="bi bi-bar-chart"></i> Reports</a>
      </div>
    </div>

    <!-- Recently Borrowed Table -->
    <div class="mt-5">
      <div class="d-flex justify-content-between align-items-center">
        <h3 class="mb-0">Recently Borrowed</h3>
        <a href="borrowed_list.php" class="btn btn-outline-primary btn-sm">
          See all <i class="bi bi-arrow-right"></i>
        </a>
      </div>

      <div class="table-responsive mt-3">
        <table class="table table-striped table-bordered align-middle">
          <thead class="table-light">
            <tr>
              <th>Book Title</th>
              <th>Student</th>
              <th>Date Borrowed</th>
              <th>Due Date</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <!-- Tip: Populate rows from your DB result -->
            <tr>
              <td>Introduction to Algorithms</td>
              <td>Juan Dela Cruz</td>
              <td>2025-10-20</td>
              <td>2025-11-03</td>
              <td><span class="badge bg-warning text-dark">Borrowed</span></td>
            </tr>
            <tr>
              <td>Database Systems</td>
              <td>Maria Santos</td>
              <td>2025-10-15</td>
              <td>2025-10-29</td>
              <td><span class="badge bg-danger">Overdue</span></td>
            </tr>
            <tr>
              <td>Clean Code</td>
              <td>Jose Rizal</td>
              <td>2025-10-10</td>
              <td>2025-10-24</td>
              <td><span class="badge bg-success">Returned</span></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

  </main>

  <footer class="text-center py-3 mt-5">
    <small>&copy; 2025 Library Management System | Admin Dashboard</small>
  </footer>

  <!-- Bootstrap JS (needed for navbar collapse) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
