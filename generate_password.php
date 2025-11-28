<?php
// Gunakan skrip ini untuk membuat hash password yang aman.
// Jalankan dari terminal: php generate_password.php

$password_plain = 'admin';
$password_hashed = password_hash($password_plain, PASSWORD_DEFAULT);

echo "Password Plain: " . $password_plain . "\n";
echo "Password Hashed: " . $password_hashed . "\n";

// Contoh cara verifikasi (seperti di login_process.php)
if (password_verify($password_plain, $password_hashed)) {
    echo "Verifikasi Berhasil!\n";
} else {
    echo "Verifikasi Gagal.\n";
}
?>
