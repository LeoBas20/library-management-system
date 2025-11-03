<?php
session_start();
include('dbcon.php');

if (empty($_SESSION['user_id']) || (($_SESSION['role'] ?? '') !== 'student')) {
  header('Location: student_login.php');
  exit;
}

$uid = $_SESSION['user_id'];
$bid = (int)($_POST['book_id'] ?? 0);
$qty = (int)($_POST['qty'] ?? 1);

if ($bid <= 0) {
  header('Location: student_books.php?err=invalid');
  exit;
}

// check if already pending or borrowed
$check = mysqli_query($connection, "
  SELECT 1 FROM transactions 
  WHERE user_id='$uid' AND book_id=$bid AND status IN ('pending','borrowed')
");
if (mysqli_num_rows($check) > 0) {
  header('Location: student_books.php?err=exists');
  exit;
}

// insert pending request
$sql = "
  INSERT INTO transactions (user_id, book_id, qty, issue_date, return_date, status)
  VALUES ('$uid', $bid, $qty, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY), 'pending')
";
if (mysqli_query($connection, $sql)) {
  header('Location: student_books.php?msg=pending');
} else {
  header('Location: student_books.php?err=failed');
}
exit;
?>
