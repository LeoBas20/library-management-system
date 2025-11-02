<?php
session_start();
include('dbcon.php');

// Guard: only logged-in student
if (empty($_SESSION['user_id']) || (($_SESSION['role'] ?? '') !== 'student')) {
  header('Location: student_login.php');
  exit;
}

$uid = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Student | Books</title>

  <link rel="stylesheet" href="css/index.css">
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <style>
    .container{margin-top:50px;}
    tr.selectable{cursor:pointer;}
    tr.selectable.table-active{outline:2px solid rgba(25,135,84,.35);}
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
        <li class="nav-item"><a class="nav-link active" href="student_books.php">Books</a></li>
        <li class="nav-item"><a class="nav-link" href="student_borrowed_books.php">Borrowed</a></li>
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
        <h2 class="m-0">Books</h2>

        <form method="GET" class="d-flex" role="search">
          <input type="text" name="search" class="form-control me-2" style="width:500px;"
                 placeholder="Search books..."
                 value="<?php if(isset($_GET['search'])) echo htmlspecialchars($_GET['search']); ?>" required>
          <button type="submit" class="btn btn-primary">Search</button>
        </form>

        <button id="openBorrowBtn" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#borrowModal" disabled>
          Borrow Book
        </button>
      </div>

      <table class="table table-hover table-bordered table-striped">
        <thead>
          <tr>
            <th>Title</th><th>Author</th><th>ISBN</th><th>Status</th><th>Copies</th>
          </tr>
        </thead>
        <tbody>
          <?php
            $search = isset($_GET['search']) ? trim($_GET['search']) : '';
            $like = "%".$connection->real_escape_string($search)."%";

            // Updated: Check transactions table for already borrowed books
            $sql = "
              SELECT b.book_id, b.title, b.author, b.isbn, b.quantity,
                     CASE 
                       WHEN t.book_id IS NOT NULL THEN 1 ELSE 0 
                     END AS already_borrowed
              FROM books_db b
              LEFT JOIN transactions t
                ON b.book_id = t.book_id AND t.user_id = '$uid' AND t.status = 'borrowed'
              " . ($search !== '' ? 
              "WHERE b.title LIKE '$like' OR b.author LIKE '$like' OR b.isbn LIKE '$like'" : '') . "
              ORDER BY b.title";

            $result = $connection->query($sql);
            if(!$result){ die('Invalid Query: '.$connection->error); }

            if ($result->num_rows > 0) {
              while($row = $result->fetch_assoc()){
                $quantity = (int)($row['quantity'] ?? 0);
                $status = ($quantity > 0) ? 'Available' : 'Not Available';
                $rid = (int)$row['book_id'];
                $rtitle = htmlspecialchars($row['title'] ?? '', ENT_QUOTES);
                $rauthor = htmlspecialchars($row['author'] ?? '', ENT_QUOTES);
                $risbn = htmlspecialchars($row['isbn'] ?? '', ENT_QUOTES);

                $disabled = $row['already_borrowed'] ? 'disabled' : '';
                $extra_class = $row['already_borrowed'] ? 'disabled' : 'selectable';
                $display_status = $row['already_borrowed'] ? 'Already Borrowed' : $status;

                echo '<tr class="'.$extra_class.'" 
                          data-book-id="'.$rid.'" 
                          data-book-title="'.$rtitle.'" 
                          data-available="'.$quantity.'" 
                          '.$disabled.'>
                        <td>'.$rtitle.'</td>
                        <td>'.$rauthor.'</td>
                        <td>'.$risbn.'</td>
                        <td>'.$display_status.'</td>
                        <td>'.$quantity.'</td>
                      </tr>';
              }
            } else {
              echo "<tr><td colspan='7' class='text-center text-danger'>No Record Found</td></tr>";
            }
          ?>
        </tbody>
      </table>

      <form action="borrow_book.php" method="POST">
        <div class="modal fade" id="borrowModal" tabindex="-1" aria-labelledby="borrowModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h1 class="modal-title fs-5" id="borrowModalLabel">Borrow Book</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>

              <div class="modal-body">
                <div class="mb-3">
                  <label class="form-label">Book Name</label>
                  <input id="modalBookName" type="text" name="book_name" class="form-control" readonly required>
                </div>

                <div class="mb-1">
                  <label class="form-label">Quantity</label>
                  <input id="modalQty" type="number" name="qty" class="form-control" min="1" max="1" value="1" readonly required>
                </div>
                <div class="text-muted small">Only 1 book allowed per borrow</div>

                <input type="hidden" id="modalBookId" name="book_id">
                <input type="hidden" id="modalAvailable" name="available">
              </div>

              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-success" name="borrow_submit" value="1">Borrow</button>
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
    const rows = Array.from(document.querySelectorAll('tbody tr.selectable'));
    const borrowBtn = document.getElementById('openBorrowBtn');
    const bookNameInput = document.getElementById('modalBookName');
    const qtyInput = document.getElementById('modalQty');
    const hiddenBookId = document.getElementById('modalBookId');
    const hiddenAvail = document.getElementById('modalAvailable');

    let selected = null;

    rows.forEach(row => {
      row.addEventListener('click', () => {
        if (row.classList.contains('disabled')) return; // prevent selecting borrowed
        rows.forEach(r => r.classList.remove('table-active'));
        row.classList.add('table-active');
        selected = {
          id: row.dataset.bookId,
          title: row.dataset.bookTitle,
          available: parseInt(row.dataset.available || '0', 10)
        };
        borrowBtn.disabled = selected.available <= 0;
      });
    });

    borrowBtn.addEventListener('click', e => {
      if (!selected) { e.preventDefault(); alert('Please select a book row first.'); return; }
      bookNameInput.value = selected.title;
      hiddenBookId.value  = selected.id;
      hiddenAvail.value   = selected.available;
      qtyInput.value = 1;
    });
  </script>
</body>
</html>
