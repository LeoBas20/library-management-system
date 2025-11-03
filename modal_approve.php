<?php
session_start();
include('dbcon.php');

if (empty($_SESSION['user_id']) || (($_SESSION['role'] ?? '') !== 'admin')) {
  header('Location: admin_login.php');
  exit;
}

if (!isset($_POST['modal_approve'])) {
  header('Location: admin_pending_list.php');
  exit;
}

$tid  = (int)($_POST['transaction_id'] ?? 0);
$issue_date  = $_POST['issue_date'] ?? '';
$return_date = $_POST['return_date'] ?? '';

if ($tid <= 0 || !$issue_date || !$return_date) {
  header('Location: admin_pending_list.php?err=invalid');
  exit;
}

// get book_id and qty from transaction
$q = mysqli_query($connection, "SELECT book_id, qty FROM transactions WHERE id=$tid AND status='pending' LIMIT 1");
if (mysqli_num_rows($q) === 0) {
  header('Location: admin_pending_list.php?err=notfound');
  exit;
}
$book = mysqli_fetch_assoc($q);
$book_id = (int)$book['book_id'];
$qty = (int)$book['qty'];

// check available stock
$stock = mysqli_fetch_assoc(mysqli_query($connection, "SELECT quantity FROM books_db WHERE book_id=$book_id"))['quantity'] ?? 0;
if ($stock < $qty) {
  header('Location: admin_pending_list.php?err=nostock');
  exit;
}

// deduct stock
mysqli_query($connection, "UPDATE books_db SET quantity = quantity - $qty WHERE book_id = $book_id");

// update transaction to borrowed
mysqli_query($connection, "
  UPDATE transactions 
  SET status='borrowed', issue_date='$issue_date', return_date='$return_date' 
  WHERE id=$tid
");

header('Location: admin_pending_list.php?msg=approved');
exit;
?>
