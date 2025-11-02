<?php
session_start();
include('dbcon.php');

// Auth check
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'student') {
  header('Location: student_login.php'); exit;
}

// Request validation
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['return_submit'])) {
  header('Location: student_borrowed_books.php'); exit;
}

$uid = $_SESSION['user_id'];
$tid = (int)($_POST['transaction_id'] ?? 0);
$bid = (int)($_POST['book_id'] ?? 0);
if (!$tid || !$bid) {
  header('Location: student_borrowed_books.php?msg=Invalid+request'); exit;
}

// Fetch transaction
$q = "SELECT qty, status FROM transactions WHERE id=? AND user_id=? AND book_id=? LIMIT 1";
$stmt = $connection->prepare($q);
$stmt->bind_param('isi', $tid, $uid, $bid);
$stmt->execute();
$tx = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$tx) {
  header('Location: student_borrowed_books.php?msg=Transaction+not+found'); exit;
}
if ($tx['status'] !== 'borrowed') {
  header('Location: student_borrowed_books.php?msg=Already+returned'); exit;
}

$qty = max(1, (int)$tx['qty']);

$connection->begin_transaction();

try {
  // Update transaction
  $stmt1 = $connection->prepare("UPDATE transactions SET status='returned' WHERE id=? AND user_id=?");
  $stmt1->bind_param('is', $tid, $uid);
  $stmt1->execute();

  // Update stock
  $stmt2 = $connection->prepare("UPDATE books_db SET quantity = quantity + ? WHERE book_id=?");
  $stmt2->bind_param('ii', $qty, $bid);
  $stmt2->execute();

  if ($stmt1->affected_rows && $stmt2->affected_rows) {
    $connection->commit();
    $msg = 'Book+returned+successfully';
  } else {
    $connection->rollback();
    $msg = 'Return+failed';
  }
} catch (Exception $e) {
  $connection->rollback();
  $msg = 'Return+failed';
}

header("Location: student_borrowed_books.php?msg=$msg");
exit;
