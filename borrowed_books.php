<?php
session_start();
include('dbcon.php');

if (empty($_SESSION['user_id']) || (($_SESSION['role'] ?? '') !== 'student')) {
  header('Location: student_login.php');
  exit;
}

$uid = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student | Borrowed Books</title>

    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <style>
        .container { margin-top: 50px; }
        tr.selectable{cursor:pointer;}
        tr.table-active{outline:2px solid rgba(13,110,253,.35);}
    </style>
</head>

<body>

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid px-4">
      <img src="img/pup_logo.png" alt="" style="height: 40px; width: auto; margin-right: 10px;">
      <a class="navbar-brand fw-bold" href="#">Student Dashboard</a>
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="students_dashboard.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="books.php">Books</a></li>
        <li class="nav-item"><a class="nav-link active" href="borrowed_books.php">Borrowed</a></li>
        <li class="nav-item"><a class="nav-link" href="student_profile.php">Profile</a></li>
        <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </nav>

  <main>
    <div class="container">

      <div class="d-flex justify-content-between align-items-center mb-3">
        <!-- Title -->
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
            <th>Issued</th>
            <th>Due</th>
            <th>Days Left</th>
            <th>Status</th>
          </tr>
        </thead>

        <tbody>
        <?php
          // Borrowed books query (+ ids for returning)
          $sql = "SELECT 
                    t.id AS transaction_id, t.book_id,
                    b.title, b.author, b.isbn, 
                    t.issue_date, t.return_date, t.status,
                    DATEDIFF(t.return_date, CURDATE()) AS days_left
                  FROM transactions t
                  INNER JOIN books_db b ON t.book_id = b.book_id
                  WHERE t.user_id = ?
                  ORDER BY t.issue_date DESC";

          $stmt = mysqli_prepare($connection, $sql);
          mysqli_stmt_bind_param($stmt, "s", $uid);
          mysqli_stmt_execute($stmt);
          $result = mysqli_stmt_get_result($stmt);

          if (mysqli_num_rows($result) > 0):
            while ($row = mysqli_fetch_assoc($result)):
              $isBorrowed = ($row['status'] === 'borrowed');
              $trClass = $isBorrowed ? 'selectable' : '';
              $dataAttr = $isBorrowed
                ? ' data-book-id="'.(int)$row['book_id'].
                  '" data-trans-id="'.(int)$row['transaction_id'].
                  '" data-book-title="'.htmlspecialchars($row['title'] ?? '', ENT_QUOTES).'"'
                : '';
        ?>
          <tr class="<?= $trClass ?>"<?= $dataAttr ?>>
            <td><?= htmlspecialchars($row['title']) ?></td>
            <td><?= htmlspecialchars($row['author']) ?></td>
            <td><?= htmlspecialchars($row['isbn']) ?></td>
            <td><?= htmlspecialchars($row['issue_date']) ?></td>
            <td><?= htmlspecialchars($row['return_date']) ?></td>
            <td>
              <?php
                if ($row['status'] !== 'borrowed') {
                  echo '—';
                } else {
                  echo ($row['days_left'] >= 0) ? $row['days_left'].' day(s)' : '<span class="text-danger">Overdue</span>';
                }
              ?>
            </td>
            <td>
              <?php if ($isBorrowed): ?>
                <span class="badge bg-warning text-dark">Borrowed</span>
              <?php else: ?>
                <span class="badge bg-success">Returned</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php
            endwhile;
          else:
        ?>
          <tr>
            <td colspan="7" class="text-center text-muted">No borrowed books found.</td>
          </tr>
        <?php
          endif;

          mysqli_stmt_close($stmt);
        ?>
        </tbody>
      </table>

      <!-- Modal (posts to return_book.php) -->
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
    const rows = Array.from(document.querySelectorAll('tbody tr.selectable'));
    const borrowBtn = document.getElementById('openBorrowBtn');

    const bookNameInput = document.getElementById('modalBookName');
    const hiddenBookId  = document.getElementById('modalBookId');
    const hiddenTransId = document.getElementById('modalTransId');

    rows.forEach(row => {
      row.addEventListener('click', () => {
        rows.forEach(r => r.classList.remove('table-active'));
        row.classList.add('table-active');
        selected = {
          bookId:  row.dataset.bookId,
          transId: row.dataset.transId,
          title:   row.dataset.bookTitle
        };
        borrowBtn.disabled = false;
      });
    });

    borrowBtn.addEventListener('click', (e) => {
      if (!selected) { e.preventDefault(); alert('Please select a borrowed row first.'); return; }
      bookNameInput.value = selected.title || '';
      hiddenBookId.value  = selected.bookId || '';
      hiddenTransId.value = selected.transId || '';
    });
  </script>

</body>
</html>
