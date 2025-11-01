<?php
session_start();
include('dbcon.php');

if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
  header('Location: admin_login.php');
  exit;
}

if (!isset($_POST['update_book'])) {
  header('Location: admin_books.php');
  exit;
}

$id     = (int)$_POST['id'];
$title  = trim($_POST['title']);
$author = trim($_POST['author']);
$isbn   = trim($_POST['isbn']);
$copies = (int)$_POST['copies'];

if (!$id || !$title || !$author || !$isbn) {
  header('Location: admin_books.php');
  exit;
}

$sql = "UPDATE books_db 
        SET title='$title', author='$author', isbn='$isbn', quantity=$copies 
        WHERE book_id=$id";

header('Location: admin_books.php?msg=' . (mysqli_query($connection, $sql) ? 'updated' : 'failed'));
exit;
?>
