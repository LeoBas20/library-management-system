<?php
session_start();
include('dbcon.php');

if (empty($_SESSION['user_id']) || (($_SESSION['role'] ?? '') !== 'admin')) {
  header('Location: admin_login.php');
  exit;
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
  header('Location: admin_pending_list.php?err=invalid');
  exit;
}

// update status to rejected
mysqli_query($connection, "
  UPDATE transactions 
  SET status='rejected' 
  WHERE id=$id AND status='pending'
");

header('Location: admin_pending_list.php?msg=rejected');
exit;
?>
