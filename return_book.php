<?php
session_start();
include('dbcon.php');

if (empty($_SESSION['user_id']) || (($_SESSION['role'] ?? '') !== 'student')) {
  header('Location: student_login.php');
  exit;
}

if (empty($_POST['return_submit'])) {
  header('Location: student_borrowed_books.php');
  exit;
}

$uid = $_SESSION['user_id'];
$tid = (int)$_POST['transaction_id'];
$bid = (int)$_POST['book_id'];

/* Validate transaction — allow borrowed or overdue */
$res = mysqli_query($connection, "
  SELECT qty FROM transactions 
  WHERE id = $tid 
    AND user_id = '$uid' 
    AND book_id = $bid 
    AND status IN ('borrowed','overdue')
  LIMIT 1
");

if ($res && mysqli_num_rows($res) > 0) {
  $row = mysqli_fetch_assoc($res);
  $qty = (int)$row['qty'];

/* Update transaction: mark returned and set return_date */
  mysqli_query($connection, "
    UPDATE transactions 
    SET status = 'returned', return_date = CURDATE()
    WHERE id = $tid
      AND status IN ('borrowed','overdue')
    LIMIT 1
  ");

  mysqli_query($connection, "
    UPDATE books_db 
    SET quantity = quantity + $qty 
    WHERE book_id = $bid
    LIMIT 1
  ");
}

header('Location: student_borrowed_books.php?msg=returned');
exit;
?>
