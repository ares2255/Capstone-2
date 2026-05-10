<?php
include "config/db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user  = $_POST['reg_user'];
    $email = $_POST['reg_email'] ?? '';
    $pass  = password_hash($_POST['reg_pass'], PASSWORD_DEFAULT);
    $role  = 'admin';

    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :u");
    $stmt->execute([':u' => $user]);
    if ($stmt->fetch()) {
        header("Location: register.php?error=exists"); exit();
    }

    $insert = $pdo->prepare("INSERT INTO users (username, password, role, email) VALUES (:u, :p, :r, :e)");
    if ($insert->execute([':u' => $user, ':p' => $pass, ':r' => $role, ':e' => $email])) {
        header("Location: admin_login.php?status=registered"); exit();
    } else {
        echo "Error inserting user.";
    }
}
?>
