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
  <link rel="stylesheet" href="css/datatables.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
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
        <li class="nav-item"><a class="nav-link" href="admin_pending_list.php">Request</a></li>
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

<main class="container my-5">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="m-0 fw-bold">Books</h2>
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addModal">
      <i class="bi bi-plus-circle me-1"></i> Add Book
    </button>
  </div>

  <div class="table-responsive">
    <table id="myTable" class="table table-striped table-bordered align-middle w-100">
      <thead>
        <tr>
          <th style="width:30%;">Title</th>
          <th style="width:20%;">Author</th>
          <th style="width:15%;">ISBN</th>
          <th style="width:15%;">Status</th>
          <th style="width:10%;">Copies</th>
          <th style="width:10%;" class="text-center">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $sql = "SELECT * FROM books_db ORDER BY title";
        $result = $connection->query($sql);
        if (!$result) { die('Invalid Query: '.$connection->error); }

        if ($result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
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
                <button type="button" class="btn btn-warning btn-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#editModal<?= $id ?>">
                  <i class="bi bi-pencil-square"></i>
                </button>
                <a href="delete_book.php?id=<?= $id ?>"
                   class="btn btn-danger btn-sm"
                   onclick="return confirm('Delete this book?')">
                  <i class="bi bi-trash"></i>
                </a>
              </div>
            </td>
          </tr>

          <!-- Edit Modal -->
          <div class="modal fade" id="editModal<?= $id ?>" tabindex="-1" aria-labelledby="editModalLabel<?= $id ?>" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <form action="edit_book.php" method="POST">
                  <div class="modal-header">
                    <h1 class="modal-title fs-5">Edit Book</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <input type="hidden" name="id" value="<?= $id ?>">
                    <div class="mb-3">
                      <label class="form-label">Title</label>
                      <input type="text" name="title" class="form-control" value="<?= $title ?>" required>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Author</label>
                      <input type="text" name="author" class="form-control" value="<?= $author ?>" required>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">ISBN</label>
                      <input type="text" name="isbn" class="form-control" value="<?= $isbn ?>" required>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Copies</label>
                      <input type="number" name="copies" class="form-control" value="<?= $copies ?>" required>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success" name="update_book">Save Changes</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        <?php
          }
        } else {
          echo "<tr><td colspan='6' class='text-center text-danger'>No Record Found</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </div>
</main>

<!-- Add Modal -->
<form action="add_book.php" method="POST">
  <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5">Add Book</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Author</label>
            <input type="text" name="author" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">ISBN</label>
            <input type="text" name="isbn" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Copies</label>
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
    <div class="alert-container position-fixed top-0 start-50 translate-middle-x mt-3" style="z-index:1055;width:350px;">
      <div class="alert alert-<?= $type ?> text-center py-2 m-0 shadow-sm"><?= $text ?></div>
    </div>
    <script>
      setTimeout(() => document.querySelector('.alert-container')?.remove(), 2000);
      const url = new URL(window.location);
      url.searchParams.delete('msg');
      window.history.replaceState({}, '', url);
    </script>
  <?php endif; ?>
<?php endif; ?>

<footer class="text-center py-3 mt-5">
  <small>&copy; 2025 Library Management System | Admin Dashboard</small>
</footer>

<script src="js/bootstrap.bundle.min.js"></script>
<script src="js/datatables.min.js"></script>
<script>
$(document).ready(function() {
  $('#myTable').DataTable({
    order: [[0, 'asc']],
    pageLength: 10,
    language: {
      emptyTable: "No books found."
    }
  });
});
</script>

</body>
</html>
