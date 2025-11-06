<?php
include('dbcon.php');

$token = $_POST["token"];
$token_hash = hash("sha256", $token);

$sql = "SELECT * FROM users WHERE reset_token_hash = ?";
$stmt = $connection->prepare($sql);
$stmt->bind_param("s", $token_hash);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("Token not found.");
}

if (strtotime($user["reset_token_expires_at"]) <= time()) {
    die("Token has expired.");
}

$role = $user["role"];

$password_hash = password_hash($_POST["password"], PASSWORD_DEFAULT);

$sql = "UPDATE users
        SET password = ?, reset_token_hash = NULL, reset_token_expires_at = NULL
        WHERE user_id = ?";

$stmt = $connection->prepare($sql);
$stmt->bind_param("ss", $password_hash, $user["user_id"]);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    if ($role === 'admin') {
        header("Location: admin_login.php?msg=reset_success");
    } else {
        header("Location: student_login.php?msg=reset_success");
    }
    exit;
}
?>
