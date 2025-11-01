<?php
include('dbcon.php');

if (isset($_POST['add_book'])) {
    $title  = trim($_POST['title']);
    $author = trim($_POST['author']);
    $isbn   = trim($_POST['isbn']);
    $copies = (int)$_POST['copies'];

    if ($title !== '' && $author !== '' && $isbn !== '' && $copies >= 0) {
        $stmt = $connection->prepare("INSERT INTO books_db (title, author, isbn, quantity) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $title, $author, $isbn, $copies);
        $stmt->execute();

        // Redirect back with flag
        header("Location: admin_books.php?msg=added");
        exit;
    } else {
        header("Location: admin_books.php");
        exit;
    }
}
?>
