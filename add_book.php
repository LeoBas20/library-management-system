<?php
include('dbcon.php');

if (isset($_POST['add_book'])) {
    $title  = trim($_POST['title']);
    $author = trim($_POST['author']);
    $isbn   = trim($_POST['isbn']);
    $copies = (int)$_POST['copies'];

    if ($title !== '' && $author !== '' && $isbn !== '' && $copies >= 0) {
        // Step 1: Check if ISBN already exists
        $check = $connection->prepare("SELECT book_id FROM books_db WHERE isbn = ?");
        $check->bind_param("s", $isbn);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            // Duplicate ISBN found
            header("Location: admin_books.php?msg=duplicate");
            exit;
        }

        // Step 2: Insert the book
        $stmt = $connection->prepare("INSERT INTO books_db (title, author, isbn, quantity) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $title, $author, $isbn, $copies);

        if ($stmt->execute()) {
            header("Location: admin_books.php?msg=added");
        } else {
            header("Location: admin_books.php?msg=failed");
        }
        exit;
    } else {
        header("Location: admin_books.php?msg=failed");
        exit;
    }
}
?>
