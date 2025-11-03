<?php
session_start();
include('dbcon.php');

if (empty($_SESSION['user_id']) || (($_SESSION['role'] ?? '') !== 'student')) {
  header('Location: student_login.php');
  exit;
}

if (!isset($_POST['return_submit'])) {
  header('Location: student_borrowed_books.php');
  exit;
}

$uid = $_SESSION['user_id'];
$tid = (int)($_POST['transaction_id'] ?? 0);
$bid = (int)($_POST['book_id'] ?? 0);

if (!$tid || !$bid) {
  header('Location: student_borrowed_books.php?msg=invalid');
  exit;
}

// Check if valid borrowed transaction
$res = mysqli_query($connection, "SELECT qty FROM transactions 
                                  WHERE id=$tid AND user_id='$uid' 
                                  AND book_id=$bid AND status='borrowed' LIMIT 1");
$row = mysqli_fetch_assoc($res);
if (!$row) {
  header('Location: student_borrowed_books.php?msg=notfound');
  exit;
}

$qty = (int)$row['qty'];

mysqli_query($connection, "UPDATE transactions SET status='returned' WHERE id=$tid");
mysqli_query($connection, "UPDATE books_db SET quantity = quantity + $qty WHERE book_id=$bid");

header('Location: student_borrowed_books.php?msg=returned');
exit;
?>
