<?php
session_start();
include('dbcon.php');

if (empty($_SESSION['user_id']) || (($_SESSION['role'] ?? '') !== 'admin')) {
  header('Location: admin_login.php');
  exit;
}

$uid = $_SESSION['user_id'];
$admin_name = 'Admin';

// Fetch admin name
$result = mysqli_query($connection, "SELECT name FROM users WHERE user_id='$uid' AND role='admin' LIMIT 1");
if ($row = mysqli_fetch_assoc($result)) {
  $admin_name = $row['name'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Library | Pending Requests</title>

  <link rel="stylesheet" href="css/index.css">
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="css/datatables.min.css">
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
        <li class="nav-item"><a class="nav-link active" href="admin_pending_list.php">Request</a></li>
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

<!-- Main Content -->
<main class="container my-5">

  <?php if (isset($_GET['msg'])): ?>
  <?php
    $messages = [
      'approved' => ['Book request approved successfully.', 'success'],
      'rejected' => ['Book request rejected successfully.', 'danger']
    ];
    [$text, $type] = $messages[$_GET['msg']] ?? [null, null];
  ?>
  <?php if ($text): ?>
    <div class="alert-container position-fixed top-0 start-50 translate-middle-x mt-3" style="z-index:1055;width:350px;">
      <div class="alert alert-<?= $type ?> text-center py-2 m-0 shadow-sm"><?= $text ?></div>
    </div>
    <script>setTimeout(() => document.querySelector('.alert-container')?.remove(), 2500);</script>
  <?php endif; ?>
  <?php endif; ?>

    <h2 class="fw-bold mb-3">Pending Book Requests</h2>

    <table id="myTable" class="table table-hover table-striped table-bordered align-middle">
      <thead>
        <tr>
          <th>Book Title</th>
          <th>Student</th>
          <th>Request Date</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $result = mysqli_query($connection, "
          SELECT 
            t.id AS transaction_id,
            b.title, 
            u.name AS student, 
            t.request_date,
            t.status
          FROM transactions t
          INNER JOIN books_db b ON t.book_id = b.book_id
          INNER JOIN users u ON t.user_id = u.user_id
          WHERE t.status = 'pending'
          ORDER BY t.request_date DESC, t.id DESC
        ");

        if (mysqli_num_rows($result) > 0) {
          while ($row = mysqli_fetch_assoc($result)) {
            $id = (int)$row['transaction_id'];
            echo '<tr>
                    <td>' . htmlspecialchars($row['title']) . '</td>
                    <td>' . htmlspecialchars($row['student']) . '</td>
                    <td>' . htmlspecialchars($row['request_date']) . '</td>
                    <td><span class="badge bg-secondary">' . ucfirst($row['status']) . '</span></td>
                    <td class="text-center">
                      <div class="d-flex justify-content-center gap-2">
                        <button 
                          type="button" 
                          class="btn btn-success btn-sm"
                          data-bs-toggle="modal" 
                          data-bs-target="#approveModal"
                          data-id="' . $id . '"
                          data-title="' . htmlspecialchars($row['title'], ENT_QUOTES) . '"
                          data-student="' . htmlspecialchars($row['student'], ENT_QUOTES) . '"
                          title="Approve">
                          <i class="bi bi-check-circle"></i>
                        </button>
                        <a 
                          href="admin_reject_request.php?id=' . $id . '"
                          class="btn btn-danger btn-sm"
                          onclick="return confirm(\'Reject this request?\');"
                          title="Reject">
                          <i class="bi bi-x-circle"></i>
                        </a>
                      </div>
                    </td>
                  </tr>';
          }
        }
        ?>
      </tbody>
    </table>
</main>

<!-- Approve Modal -->
<form action="modal_approve.php" method="POST">
  <div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="approveModalLabel">Approve Request</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <input type="hidden" name="transaction_id" id="modal-transaction-id">
          <div class="mb-2 small text-muted" id="modal-context"></div>

          <div class="mb-3">
            <label for="modal-issue-date" class="form-label">Issue Date</label>
            <input type="date" name="issue_date" id="modal-issue-date" class="form-control" required>
          </div>

          <div class="mb-3">
            <label for="modal-return-date" class="form-label">Return Date</label>
            <input type="date" name="return_date" id="modal-return-date" class="form-control" required>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-success" name="modal_approve" value="1">Approve</button>
        </div>
      </div>
    </div>
  </div>
</form>

<footer class="text-center py-3 mt-5">
  <small>&copy; 2025 Library Management System | Admin Dashboard</small>
</footer>

<script src="js/bootstrap.bundle.min.js"></script>
<script src="js/datatables.min.js"></script>
<script>
$(document).ready(function() {
  $('#myTable').DataTable({
    language: {
      emptyTable: "No pending request."
    }
  });
});


const approveModal = document.getElementById('approveModal');
if (approveModal) {
  approveModal.addEventListener('show.bs.modal', event => {
    const btn = event.relatedTarget;
    const id = btn.getAttribute('data-id');
    const title = btn.getAttribute('data-title');
    const student = btn.getAttribute('data-student');
    approveModal.querySelector('#modal-transaction-id').value = id;
    approveModal.querySelector('#modal-context').textContent = `Approve: "${title}" for ${student}`;
    const today = new Date().toISOString().slice(0,10);
    const plus7 = new Date(Date.now() + 7*24*3600*1000).toISOString().slice(0,10);
    approveModal.querySelector('#modal-issue-date').value = today;
    approveModal.querySelector('#modal-return-date').value = plus7;
  });
}
</script>
</body>
</html>
