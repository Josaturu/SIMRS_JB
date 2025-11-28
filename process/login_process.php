<?php
session_start();

include_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $database = new Database();
    $db = $database->getConnection();

    $username = $_POST['username'];
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        header('Location: ../login.php?error=Username dan password harus diisi');
        exit();
    }

    try {
        // Asumsi ada tabel 'users' dengan kolom 'username', 'password', dan 'role'
        // Anda perlu membuat tabel ini di database Anda
        $query = "SELECT * FROM users WHERE username = :username LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);


            // Verifikasi password (asumsi password di-hash dengan password_hash())
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                // Arahkan ke halaman daftar pasien
                header('Location: ../views/dashboard.php');
                exit();
            } else {
                header('Location: ../login.php?error=Username atau password salah');
                exit();
            }
        } else {
            header('Location: ../login.php?error=Username atau password salah');
            exit();
        }
    } catch (PDOException $e) {
        header('Location: ../login.php?error=Koneksi database gagal: ' . $e->getMessage());
        exit();
    }
} else {
    header('Location: ../login.php');
    exit();
}
?>
