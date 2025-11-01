<?php
session_start();
include('dbcon.php');

// Only admins allowed
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
  header('Location: admin_login.php');
  exit;
}

// Check ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  header('Location: admin_books.php');
  exit;
}

$id = (int)$_GET['id'];

// Delete query
$sql = "DELETE FROM books_db WHERE book_id = $id";

if (mysqli_query($connection, $sql)) {
  header('Location: admin_books.php?msg=deleted');
  exit;
} else {
  header('Location: admin_books.php?msg=failed');
  exit;
}
?>
