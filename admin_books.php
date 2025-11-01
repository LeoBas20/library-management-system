<?php
session_start();
include('dbcon.php');

if (empty($_SESSION['user_id']) || (($_SESSION['role'] ?? '') !== 'admin')) {
  header('Location: admin_login.php');
  exit;
}

$uid = $_SESSION['user_id'];
$admin_name = 'Admin';

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
  <title>Library | Books</title>

  <link rel="stylesheet" href="css/index.css">
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <style>
    .container { margin-top: 50px; }
    tr.selectable { cursor: pointer; }
    tr.table-active { outline: 2px solid rgba(25,135,84,.35); }
  </style>
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
        <div class="container-fluid px-4">
            <div class="d-flex align-items-center">
            <img src="img/pup_logo.png" alt="PUP Logo" style="height: 40px; width: auto; margin-right: 10px;">
            <a class="navbar-brand fw-bold mb-0" href="admin_dashboard.php">Library Admin Panel</a>
            </div>

            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="admin_dashboard.php">Home</a></li>
                <li class="nav-item"><a class="nav-link active" href="admin_books.php">Books</a></li>
                <li class="nav-item"><a class="nav-link" href="admin_borrowed_list.php">Borrowed</a></li>
                <li class="nav-item"><a class="nav-link" href="admin_borrowed_list.php?filter=overdue">Overdue</a></li>
                <li class="nav-item"><a class="nav-link" href="students.php">Students</a></li>
                </ul>
            </div>

            <div class="dropdown ms-3">
            <button class="btn btn-outline-dark btn-sm dropdown-toggle d-flex align-items-center" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-person-circle me-1"></i> <?= htmlspecialchars($admin_name) ?>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="admin_profile.php"><i class="bi bi-person"></i> Profile</a></li>
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

      <!-- Search Form -->
      <form method="GET" class="d-flex" role="search">
        <input
          type="text"
          name="search"
          class="form-control me-2"
          style="width:500px;"
          placeholder="Search books..."
          value="<?php if(isset($_GET['search'])) echo htmlspecialchars($_GET['search']); ?>">
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-search me-1"></i> Search
        </button>
      </form>

      <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addModal">
        <i class="bi bi-plus-circle me-1"></i> Add Book
      </button>
    </div>

    <table class="table table-hover table-bordered table-striped align-middle">
      <thead>
        <tr>
          <th>Title</th><th>Author</th><th>ISBN</th><th>Status</th><th>Copies</th><th class="text-center">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php
          $search = isset($_GET['search']) ? trim($_GET['search']) : '';
          if ($search !== '') {
            $like = "%".$connection->real_escape_string($search)."%";
            $sql = "SELECT * FROM books_db 
                    WHERE title LIKE '$like' OR author LIKE '$like' OR isbn LIKE '$like' 
                    ORDER BY title";
          } else {
            $sql = "SELECT * FROM books_db ORDER BY title";
          }

          $result = $connection->query($sql);
          if(!$result){ die('Invalid Query: '.$connection->error); }

          if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()){
              $id = (int)$row['book_id'];
              $title = htmlspecialchars($row['title']);
              $author = htmlspecialchars($row['author']);
              $isbn = htmlspecialchars($row['isbn']);
              $copies = (int)$row['quantity'];
              $status = $copies > 0 ? 'Available' : 'Not Available';
        ?>
          <tr>
            <td><?= $title ?></td>
            <td><?= $author ?></td>
            <td><?= $isbn ?></td>
            <td><?= $status ?></td>
            <td><?= $copies ?></td>
            <td class="text-center">
            <div class="d-flex justify-content-center gap-2">
                <button type="button" class="btn btn-warning btn-sm d-flex align-items-center justify-content-center"
                        data-bs-toggle="modal" data-bs-target="#editModal<?= $id ?>" data-bs-toggle="tooltip" title="Edit Book">
                <i class="bi bi-pencil-square"></i>
                </button>
                <a href="delete_book.php?id=<?= $id ?>"
                class="btn btn-danger btn-sm d-flex align-items-center justify-content-center"
                data-bs-toggle="tooltip" title="Delete Book"
                onclick="return confirm('Delete this book?')">
                <i class="bi bi-trash"></i>
                </a>
            </div>
            </td>


          <!-- Edit Modal -->
          <form action="edit_book.php" method="POST">
            <div class="modal fade" id="editModal<?= $id ?>" tabindex="-1" aria-labelledby="editModalLabel<?= $id ?>" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <h1 class="modal-title fs-5" id="editModalLabel<?= $id ?>">Edit Book</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <input type="hidden" name="id" value="<?= $id ?>">

                    <div class="mb-3">
                      <label for="title" class="form-label">Title</label>
                      <input type="text" name="title" class="form-control" value="<?= $title ?>" required>
                    </div>

                    <div class="mb-3">
                      <label for="author" class="form-label">Author</label>
                      <input type="text" name="author" class="form-control" value="<?= $author ?>" required>
                    </div>

                    <div class="mb-3">
                      <label for="isbn" class="form-label">ISBN</label>
                      <input type="text" name="isbn" class="form-control" value="<?= $isbn ?>" required>
                    </div>

                    <div class="mb-3">
                      <label for="copies" class="form-label">Copies</label>
                      <input type="number" name="copies" class="form-control" value="<?= $copies ?>" required>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <input type="submit" class="btn btn-success" name="update_book" value="Save Changes">
                  </div>
                </div>
              </div>
            </div>
          </form>
        <?php
            }
          } else {
            echo "<tr><td colspan='6' class='text-center text-danger'>No Record Found</td></tr>";
          }
        ?>
      </tbody>
    </table>

    <!-- Add Modal -->
    <form action="add_book.php" method="POST">
      <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h1 class="modal-title fs-5" id="addModalLabel">Add Book</h1>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
              <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" name="title" class="form-control" required>
              </div>

              <div class="mb-3">
                <label for="author" class="form-label">Author</label>
                <input type="text" name="author" class="form-control" required>
              </div>

              <div class="mb-3">
                <label for="isbn" class="form-label">ISBN</label>
                <input type="text" name="isbn" class="form-control" required>
              </div>

              <div class="mb-3">
                <label for="copies" class="form-label">Copies</label>
                <input type="number" name="copies" class="form-control" required>
              </div>
            </div>

            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              <input type="submit" class="btn btn-success" name="add_book" value="Add">
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
</main>

<?php if (isset($_GET['msg'])): ?>
  <?php
    $messages = [
      'added'   => ['Book added successfully.', 'success'],
      'updated' => ['Book updated successfully.', 'success'],
      'deleted' => ['Book deleted successfully.', 'danger'],
      'failed'  => ['Action failed.', 'danger']
    ];
    [$text, $type] = $messages[$_GET['msg']] ?? [null, null];
  ?>
  <?php if ($text): ?>
    <style>
      .alert-container {
        transition: opacity 0.6s ease;
      }
      .fade-out {
        opacity: 0;
      }
    </style>

    <div class="alert-container position-fixed top-0 start-50 translate-middle-x mt-3"
         style="z-index:1055;width:350px;">
      <div class="alert alert-<?= $type ?> text-center py-2 m-0 shadow-sm"><?= $text ?></div>
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
      }, 1500);
    </script>
  <?php endif; ?>
<?php endif; ?>

<footer class="text-center py-3 mt-5">
  <small>&copy; 2025 Library Management System | Admin Dashboard</small>
</footer>

<script src="js/bootstrap.bundle.min.js"></script>
<script>
    const searchBox = document.querySelector('input[name="search"]');
    searchBox.addEventListener('input', () => { if (searchBox.value === '') window.location = 'admin_books.php'; });
</script>
</body>
</html>
