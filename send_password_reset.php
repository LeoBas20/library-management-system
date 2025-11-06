<?php
include('dbcon.php');

$email = $_POST["email"];
$token = bin2hex(random_bytes(16));
$token_hash = hash("sha256", $token);
$expiry = date("Y-m-d H:i:s", time() + 60 * 30);

$sql = "UPDATE users
        SET reset_token_hash = ?,
            reset_token_expires_at = ?
        WHERE email = ?";

$stmt = $connection->prepare($sql);
$stmt->bind_param("sss", $token_hash, $expiry, $email);
$stmt->execute();

if ($stmt->affected_rows) {
    require __DIR__ . "/mailer.php";

    $mail->setFrom("noreply@example.com");
    $mail->addAddress($email);
    $mail->Subject = "Password Reset";
    $mail->isHTML(true);
    $mail->Body = "
    <p>We received a request to reset your password for your PUP Library Portal account.</p>

    <p>Click <a href='http://localhost/Library/reset_password.php?token=$token'>here</a> to reset your password.</p>

    <p>This link will expire in 30 minutes.</p>

    <p>If you didn't request this change, you can safely ignore this email.</p>

    <p>- PUP Library Management System</p>
    ";

    try {
        $mail->send();
        header("Location: forgot_password.php?msg=sent");
        exit;
    } catch (Exception $e) {
        header("Location: forgot_password.php?msg=failed");
        exit;
    }
}
?>
