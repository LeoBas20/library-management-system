<?php /* no auth code here to keep your structure; borrow_book.php will guard */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Books</title>

  <link rel="stylesheet" href="css/index.css">
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="css/datatables.min.css">
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
      <img src="img/pup_logo.png" alt="" style="height:40px;width:auto;margin-right:10px;">
      <a class="navbar-brand fw-bold" href="#">Student Dashboard</a>
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="students_dashboard.php">Home</a></li>
        <li class="nav-item"><a class="nav-link active" href="books.php">Books</a></li>
        <li class="nav-item"><a class="nav-link" href="borrowed_books.php">Borrowed</a></li>
        <li class="nav-item"><a class="nav-link" href="student_profile.php">Profile</a></li>
        <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </nav>

  <main>
    <div class="container">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="m-0">Books</h2>

        <!-- Search Form -->
        <form method="GET" class="d-flex" role="search">
          <input
            type="text"
            name="search"
            class="form-control me-2"
            style="width:500px;"
            placeholder="Search books..."
            value="<?php if(isset($_GET['search'])) echo htmlspecialchars($_GET['search']); ?>"
            required>
          <button type="submit" class="btn btn-primary">Search</button>
        </form>

        <!-- Borrow button opens modal (enabled after row select) -->
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
            include('dbcon.php');

            $search = isset($_GET['search']) ? trim($_GET['search']) : '';
            if ($search !== '') {
              $like = "%".$connection->real_escape_string($search)."%";
              $sql = "SELECT book_id, title, author, isbn, quantity
                      FROM books_db
                      WHERE title LIKE '$like' OR author LIKE '$like' OR isbn LIKE '$like'
                      ORDER BY title";
            } else {
              $sql = "SELECT book_id, title, author, isbn, quantity FROM books_db ORDER BY title";
            }
            $result = $connection->query($sql);
            if(!$result){ die('Invalid Query: '.$connection->error); }

            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()){
                $quantity = (int)($row['quantity'] ?? 0);
                $status   = ($quantity > 0) ? 'Available' : 'Not Available';

                $rid     = (int)$row['book_id'];
                $rtitle  = htmlspecialchars($row['title'] ?? '', ENT_QUOTES);
                $rauthor = htmlspecialchars($row['author'] ?? '', ENT_QUOTES);
                $risbn   = htmlspecialchars($row['isbn'] ?? '', ENT_QUOTES);

                echo '<tr class="selectable"
                            data-book-id="'.$rid.'"
                            data-book-title="'.$rtitle.'"
                            data-available="'.$quantity.'">
                        <td>'.$rtitle.'</td>
                        <td>'.$rauthor.'</td>
                        <td>'.$risbn.'</td>
                        <td>'.$status.'</td>
                        <td>'.$quantity.'</td>
                        </tr>';
                }
            } else {
                        echo "<tr><td colspan='7' class='text-center text-danger'>No Record Found</td></tr>";
                    }
          ?>
        </tbody>
      </table>

      <!-- Modal (posts to borrow_book.php) -->
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
                  <input id="modalQty" type="number" name="qty" class="form-control" min="1" required>
                </div>
                <div class="text-muted small">Available: <span id="modalAvail">0</span></div>

                <!-- Hidden -->
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
    // reset to full list when clearing search
    const searchBox = document.querySelector('input[name="search"]');
    searchBox.addEventListener('input', () => { if (searchBox.value === '') window.location = 'books.php'; });

    // selection + modal population
    let selected = null;
    const rows = Array.from(document.querySelectorAll('tbody tr.selectable'));
    const borrowBtn = document.getElementById('openBorrowBtn');

    const bookNameInput = document.getElementById('modalBookName');
    const qtyInput      = document.getElementById('modalQty');
    const availSpan     = document.getElementById('modalAvail');
    const hiddenBookId  = document.getElementById('modalBookId');
    const hiddenAvail   = document.getElementById('modalAvailable');

    rows.forEach(row => {
      row.addEventListener('click', () => {
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

    borrowBtn.addEventListener('click', (e) => {
      if (!selected) { e.preventDefault(); alert('Please select a book row first.'); return; }
      bookNameInput.value = selected.title;
      hiddenBookId.value  = selected.id;
      hiddenAvail.value   = selected.available;
      availSpan.textContent = selected.available;

      if (selected.available > 0) {
        qtyInput.min = 1;
        qtyInput.max = selected.available;
        qtyInput.value = 1;
        qtyInput.removeAttribute('readonly');
      } else {
        qtyInput.value = 0;
        qtyInput.min = 0; qtyInput.max = 0;
        qtyInput.setAttribute('readonly','readonly');
      }
    });

    qtyInput?.addEventListener('input', () => {
      const a = parseInt(hiddenAvail.value || '0', 10);
      let v = parseInt(qtyInput.value || '0', 10);
      if (isNaN(v) || v < 1) v = 1;
      if (v > a) v = a;
      qtyInput.value = v;
    });
  </script>
</body>
</html>
