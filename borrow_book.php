<?php
// borrow_book.php
session_start();
require 'dbcon.php';                       // must select the 'library' DB

if (empty($_SESSION['user_id'])) { header('Location: student_login.php'); exit; }

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$connection->set_charset('utf8mb4');

$uid = (string)($_SESSION['user_id']);     // users.user_id is VARCHAR(20)
$bid = (int)($_POST['book_id'] ?? 0);
$qty = (int)($_POST['qty'] ?? 0);

if ($bid <= 0 || $qty <= 0) { header('Location: books.php?err=badinput'); exit; }

try {
  $connection->begin_transaction();

  // 1) Atomic stock decrement in books_db; only proceed if enough copies
  $u = $connection->prepare(
    "UPDATE books_db
        SET quantity = quantity - ?
      WHERE book_id = ? AND quantity >= ?"
  );
  $u->bind_param('iii', $qty, $bid, $qty);
  $u->execute();
  if ($connection->affected_rows === 0) {
    throw new Exception('insufficient');   // not enough stock or wrong book_id
  }

  // 2) Log the borrow in transactions (with qty)
  $i = $connection->prepare("
    INSERT INTO transactions (user_id, book_id, qty, issue_date, return_date, status)
    VALUES (?, ?, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY), 'borrowed')
  ");
  $i->bind_param('sii', $uid, $bid, $qty);
  $i->execute();

  $connection->commit();
  header('Location: books.php?ok=1'); exit;

} catch (Throwable $e) {
  $connection->rollback();
  $code = ($e->getMessage() === 'insufficient') ? 'insufficient' : 'dberror';
  header('Location: books.php?err='.$code); exit;
}
