<?php
session_start();
include('dbcon.php');

if (empty($_SESSION['user_id']) || (($_SESSION['role'] ?? '') !== 'student')) {
  header('Location: student_login.php');
  exit;
}

$uid = $_SESSION['user_id'];

// Auto-update overdue
mysqli_query($connection, "
  UPDATE transactions
  SET status = 'overdue'
  WHERE user_id = '$uid'
    AND status = 'borrowed'
    AND return_date < CURDATE()
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student | Borrowed Books</title>
  <link rel="stylesheet" href="css/index.css">
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <style>
    .container { margin-top: 50px; }
    tr.selectable { cursor: pointer; }
    tr.table-active { outline: 2px solid rgba(13,110,253,.35); }
  </style>
</head>

<body>
<!-- Navbar -->
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
        <li class="nav-item"><a class="nav-link active" href="student_borrowed_books.php">Borrowed</a></li>
      </ul>
    </div>

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

<main>
  <div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2 class="m-0">My Borrowed Books</h2>
      <button id="openBorrowBtn" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#returnModal" disabled>
        Return Book
      </button>
    </div>

    <table class="table table-hover table-bordered table-striped">
      <thead>
        <tr>
          <th>Title</th>
          <th>Author</th>
          <th>ISBN</th>
          <th>Request Date</th>
          <th>Issued</th>
          <th>Due</th>
          <th>Days Left</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $sql = "SELECT 
                  t.id AS transaction_id, t.book_id,
                  b.title, b.author, b.isbn,
                  t.request_date, t.issue_date, t.return_date, t.status,
                  DATEDIFF(t.return_date, CURDATE()) AS days_left
                FROM transactions t
                INNER JOIN books_db b ON t.book_id = b.book_id
                WHERE t.user_id = ?
                ORDER BY FIELD(t.status, 'pending','borrowed','overdue','returned','rejected'), t.id DESC";
        $stmt = mysqli_prepare($connection, $sql);
        mysqli_stmt_bind_param($stmt, 's', $uid);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0):
          while ($row = mysqli_fetch_assoc($result)):
            $status = $row['status'];
            $isBorrowed = ($status === 'borrowed');
            $trClass = $isBorrowed ? 'selectable' : '';
            $dataAttr = $isBorrowed
              ? ' data-book-id="'.(int)$row['book_id'].'"
                  data-trans-id="'.(int)$row['transaction_id'].'"
                  data-book-title="'.htmlspecialchars($row['title'], ENT_QUOTES).'"'
              : '';
        ?>
        <tr class="<?= $trClass ?>"<?= $dataAttr ?>>
          <td><?= htmlspecialchars($row['title']) ?></td>
          <td><?= htmlspecialchars($row['author']) ?></td>
          <td><?= htmlspecialchars($row['isbn']) ?></td>
          <td><?= htmlspecialchars($row['request_date']) ?></td>
          <td>
            <?= ($status === 'pending' || $status === 'rejected') ? '—' : ($row['issue_date'] ?: '—') ?>
          </td>
          <td>
            <?= ($status === 'pending' || $status === 'rejected') ? '—' : ($row['return_date'] ?: '—') ?>
          </td>
          <td><?= $isBorrowed ? max(0, $row['days_left']).' day(s)' : '—' ?></td>
          <td>
            <?php
              if ($status === 'pending')   echo '<span class="badge bg-secondary">Pending</span>';
              elseif ($status === 'borrowed') echo '<span class="badge bg-warning text-dark">Borrowed</span>';
              elseif ($status === 'overdue')  echo '<span class="badge bg-danger">Overdue</span>';
              elseif ($status === 'returned') echo '<span class="badge bg-success">Returned</span>';
              elseif ($status === 'rejected') echo '<span class="badge bg-dark text-white">Rejected</span>';
            ?>
          </td>
        </tr>
        <?php
          endwhile;
        else:
          echo '<tr><td colspan="8" class="text-center text-muted">No records found.</td></tr>';
        endif;
        mysqli_stmt_close($stmt);
        ?>
      </tbody>
    </table>

    <!-- Return Modal -->
    <form action="return_book.php" method="POST">
      <div class="modal fade" id="returnModal" tabindex="-1" aria-labelledby="returnModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h1 class="modal-title fs-5" id="returnModalLabel">Return Book</h1>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div class="mb-3">
                <label class="form-label">Book to return</label>
                <input id="modalBookName" type="text" class="form-control" readonly>
              </div>
              <input type="hidden" id="modalBookId"  name="book_id">
              <input type="hidden" id="modalTransId" name="transaction_id">
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              <button type="submit" class="btn btn-success" name="return_submit" value="1">Confirm Return</button>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
</main>

<footer class="text-center py-3 mt-5">
  <small>&copy; 2025 Library Management System | Student Dashboard</small>
</footer>

<script src="js/bootstrap.bundle.min.js"></script>
<script>
let selected = null;
const rows = document.querySelectorAll('tbody tr.selectable');
const returnBtn = document.getElementById('openBorrowBtn');
const bookNameInput = document.getElementById('modalBookName');
const hiddenBookId  = document.getElementById('modalBookId');
const hiddenTransId = document.getElementById('modalTransId');

rows.forEach(row => {
  row.addEventListener('click', () => {
    rows.forEach(r => r.classList.remove('table-active'));
    row.classList.add('table-active');
    selected = {
      bookId: row.dataset.bookId,
      transId: row.dataset.transId,
      title: row.dataset.bookTitle
    };
    returnBtn.disabled = false;
  });
});

returnBtn.addEventListener('click', e => {
  if (!selected) { e.preventDefault(); return; }
  bookNameInput.value = selected.title;
  hiddenBookId.value  = selected.bookId;
  hiddenTransId.value = selected.transId;
});
</script>
</body>
</html>
