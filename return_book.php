<?php
session_start();
include('dbcon.php');

if (empty($_SESSION['user_id']) || (($_SESSION['role'] ?? '') !== 'student')) {
  header('Location: student_login.php'); exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['return_submit'])) {
  header('Location: borrowed_books.php'); exit;
}

$uid = $_SESSION['user_id'];
$tid = (int)($_POST['transaction_id'] ?? 0);
$bid = (int)($_POST['book_id'] ?? 0);
if ($tid <= 0 || $bid <= 0) { header('Location: borrowed_books.php?msg=Invalid+request'); exit; }

// Get transaction (must be yours and still borrowed)
$stmt = $connection->prepare("SELECT qty, status FROM transactions WHERE id = ? AND user_id = ? AND book_id = ? LIMIT 1");
$stmt->bind_param('isi', $tid, $uid, $bid);
$stmt->execute();
$tx = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$tx) { header('Location: borrowed_books.php?msg=Transaction+not+found'); exit; }
if ($tx['status'] !== 'borrowed') { header('Location: borrowed_books.php?msg=Already+returned'); exit; }
$qty = max(1, (int)$tx['qty']);

$connection->begin_transaction();

// Mark returned
$u1 = $connection->prepare("UPDATE transactions SET status='returned' WHERE id=? AND user_id=? AND status='borrowed' LIMIT 1");
$u1->bind_param('is', $tid, $uid);
$u1->execute();

// Put back stock
$u2 = $connection->prepare("UPDATE books_db SET quantity = quantity + ? WHERE book_id = ? LIMIT 1");
$u2->bind_param('ii', $qty, $bid);
$u2->execute();

if ($u1->affected_rows === 1 && $u2->affected_rows === 1) {
  $connection->commit();
  header('Location: borrowed_books.php?msg=Book+returned+successfully');
} else {
  $connection->rollback();
  header('Location: borrowed_books.php?msg=Return+failed');
}
exit;
