<?php
// Submit Form Keselamatan Operasi - Production Version
require_once '../config/database.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        if (!$db) {
            throw new Exception("Database connection failed");
        }
        
        // Ambil data form
        $formData = $_POST;
        
        // Helper function: convert checkbox to tinyint 1/0
        function checkboxToBool($value) {
            return (!empty($value) && $value !== 'false') ? 1 : 0;
        }
        
        // Generate UUID untuk ID
        $id = sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
        
        // Mapping Sign In checklist (14 items)
        $signin_1a = checkboxToBool($formData['signin_1a'] ?? null);
        $signin_1b = checkboxToBool($formData['signin_1b'] ?? null);
        $signin_1c = checkboxToBool($formData['signin_1c'] ?? null);
        $signin_1d = checkboxToBool($formData['signin_1d'] ?? null);
        $signin_2a = checkboxToBool($formData['signin_2a'] ?? null);
        $signin_2b = checkboxToBool($formData['signin_2b'] ?? null);
        $signin_3 = checkboxToBool($formData['signin_3'] ?? null);
        $signin_4 = checkboxToBool($formData['signin_4'] ?? null);
        $signin_5a = checkboxToBool($formData['signin_5a'] ?? null);
        $signin_5b = checkboxToBool($formData['signin_5b'] ?? null);
        $signin_6a = checkboxToBool($formData['signin_6a'] ?? null);
        $signin_6b = checkboxToBool($formData['signin_6b'] ?? null);
        $signin_7a = checkboxToBool($formData['signin_7a'] ?? null);
        $signin_7b = checkboxToBool($formData['signin_7b'] ?? null);
        
        // Mapping Time Out checklist (7 items)
        $timeout_1 = checkboxToBool($formData['timeout_1'] ?? null);
        $timeout_2a = checkboxToBool($formData['timeout_2a'] ?? null);
        $timeout_2b = checkboxToBool($formData['timeout_2b'] ?? null);
        $timeout_2c = checkboxToBool($formData['timeout_2c'] ?? null);
        $timeout_3 = checkboxToBool($formData['timeout_3'] ?? null);
        $timeout_5a = checkboxToBool($formData['timeout_5a'] ?? null);
        $timeout_5b = checkboxToBool($formData['timeout_5b'] ?? null);
        
        // Mapping Sign Out checklist (5 items)
        $signout_1a = checkboxToBool($formData['signout_1a'] ?? null);
        $signout_1b = checkboxToBool($formData['signout_1b'] ?? null);
        $signout_1c = checkboxToBool($formData['signout_1c'] ?? null);
        $signout_1d = checkboxToBool($formData['signout_1d'] ?? null);
        $signout_2 = checkboxToBool($formData['signout_2'] ?? null);
        
        // Cek apakah data sudah ada
        $check_query = "SELECT id FROM tbl_anestesi_keselamatan_operasi 
                        WHERE no_rawat = ? AND kode_paket = ? AND tanggal = ? AND jam_mulai = ?";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->execute([$formData['no_rawat'], $formData['kode_paket'], $formData['tanggal'], $formData['jam_mulai']]);
        $existing = $check_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existing) {
            // UPDATE existing data
            $query = "UPDATE tbl_anestesi_keselamatan_operasi SET
                       operasi = :operasi, tanggal_tindakan = :tanggal_tindakan,
                       nama_pasien = :nama_pasien, no_rekam_medis = :no_rekam_medis, 
                       tgl_lahir_umur = :tgl_lahir_umur, alamat = :alamat, operator = :operator,
                       signin_time = :signin_time, signin_1a = :signin_1a, signin_1b = :signin_1b, 
                       signin_1c = :signin_1c, signin_1d = :signin_1d, signin_2a = :signin_2a, 
                       signin_2b = :signin_2b, signin_3 = :signin_3, signin_4 = :signin_4, 
                       signin_5a = :signin_5a, signin_5b = :signin_5b, signin_6a = :signin_6a, 
                       signin_6b = :signin_6b, signin_7a = :signin_7a, signin_7b = :signin_7b,
                       dokter_anestesi_signin = :dokter_anestesi_signin, 
                       perawat_anestesi_signin = :perawat_anestesi_signin, 
                       perawat_sirkuler_signin = :perawat_sirkuler_signin,
                       timeout_time = :timeout_time, timeout_1 = :timeout_1, timeout_2a = :timeout_2a, 
                       timeout_2b = :timeout_2b, timeout_2c = :timeout_2c, timeout_3 = :timeout_3,
                       catatan_dokter_bedah = :catatan_dokter_bedah, 
                       catatan_dokter_anestesi = :catatan_dokter_anestesi, 
                       catatan_perawat = :catatan_perawat,
                       timeout_5a = :timeout_5a, timeout_5b = :timeout_5b, 
                       perawat_sirkuler_timeout = :perawat_sirkuler_timeout,
                       signout_time = :signout_time, signout_1a = :signout_1a, signout_1b = :signout_1b, 
                       signout_1c = :signout_1c, signout_1d = :signout_1d, signout_2 = :signout_2,
                       tanggal_keluar = :tanggal_keluar, tahun_keluar = :tahun_keluar,
                       perawat_sirkuler_signout = :perawat_sirkuler_signout, 
                       dokter_anestesi_signout = :dokter_anestesi_signout, 
                       operator_signout = :operator_signout
                      WHERE no_rawat = :no_rawat AND kode_paket = :kode_paket 
                        AND tanggal = :tanggal AND jam_mulai = :jam_mulai";
        } else {
            // INSERT new data
            $id = sprintf(
                '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0x0fff) | 0x4000,
                mt_rand(0, 0x3fff) | 0x8000,
                mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
            );
        }
        
        // Prepare query (INSERT or UPDATE)
        if (!$existing) {
        $query = "INSERT INTO tbl_anestesi_keselamatan_operasi (
                   id, no_rawat, kode_paket, tanggal, jam_mulai, operasi, tanggal_tindakan,
                   nama_pasien, no_rekam_medis, tgl_lahir_umur, alamat, operator,
                   signin_time, signin_1a, signin_1b, signin_1c, signin_1d, 
                   signin_2a, signin_2b, signin_3, signin_4, 
                   signin_5a, signin_5b, signin_6a, signin_6b, signin_7a, signin_7b,
                   dokter_anestesi_signin, perawat_anestesi_signin, perawat_sirkuler_signin,
                   timeout_time, timeout_1, timeout_2a, timeout_2b, timeout_2c, timeout_3,
                   catatan_dokter_bedah, catatan_dokter_anestesi, catatan_perawat,
                   timeout_5a, timeout_5b, perawat_sirkuler_timeout,
                   signout_time, signout_1a, signout_1b, signout_1c, signout_1d, signout_2,
                   tanggal_keluar, tahun_keluar,
                   perawat_sirkuler_signout, dokter_anestesi_signout, operator_signout
                  ) VALUES (
                   :id, :no_rawat, :kode_paket, :tanggal, :jam_mulai, :operasi, :tanggal_tindakan,
                   :nama_pasien, :no_rekam_medis, :tgl_lahir_umur, :alamat, :operator,
                   :signin_time, :signin_1a, :signin_1b, :signin_1c, :signin_1d,
                   :signin_2a, :signin_2b, :signin_3, :signin_4,
                   :signin_5a, :signin_5b, :signin_6a, :signin_6b, :signin_7a, :signin_7b,
                   :dokter_anestesi_signin, :perawat_anestesi_signin, :perawat_sirkuler_signin,
                   :timeout_time, :timeout_1, :timeout_2a, :timeout_2b, :timeout_2c, :timeout_3,
                   :catatan_dokter_bedah, :catatan_dokter_anestesi, :catatan_perawat,
                   :timeout_5a, :timeout_5b, :perawat_sirkuler_timeout,
                   :signout_time, :signout_1a, :signout_1b, :signout_1c, :signout_1d, :signout_2,
                   :tanggal_keluar, :tahun_keluar,
                   :perawat_sirkuler_signout, :dokter_anestesi_signout, :operator_signout
                  )";
        }
        
        $stmt = $db->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . print_r($db->errorInfo(), true));
        }
        
        // Bind parameters - Metadata
        if (!$existing) {
            $stmt->bindParam(':id', $id);
        }
        $stmt->bindParam(':no_rawat', $formData['no_rawat']);
        $stmt->bindParam(':kode_paket', $formData['kode_paket']);
        $stmt->bindParam(':tanggal', $formData['tanggal']);
        $stmt->bindParam(':jam_mulai', $formData['jam_mulai']);
        $stmt->bindParam(':operasi', $formData['operasi']);
        $stmt->bindParam(':tanggal_tindakan', $formData['tglTindakan']);
        
        // Bind parameters - Informasi Pasien
        $stmt->bindParam(':nama_pasien', $formData['namaPasien']);
        $stmt->bindParam(':no_rekam_medis', $formData['noRekamMedis']);
        $stmt->bindParam(':tgl_lahir_umur', $formData['tglLahir']);
        $stmt->bindParam(':alamat', $formData['alamat']);
        $stmt->bindParam(':operator', $formData['operator']);
        
        // Bind parameters - Sign In
        $stmt->bindParam(':signin_time', $formData['signin_time']);
        $stmt->bindParam(':signin_1a', $signin_1a);
        $stmt->bindParam(':signin_1b', $signin_1b);
        $stmt->bindParam(':signin_1c', $signin_1c);
        $stmt->bindParam(':signin_1d', $signin_1d);
        $stmt->bindParam(':signin_2a', $signin_2a);
        $stmt->bindParam(':signin_2b', $signin_2b);
        $stmt->bindParam(':signin_3', $signin_3);
        $stmt->bindParam(':signin_4', $signin_4);
        $stmt->bindParam(':signin_5a', $signin_5a);
        $stmt->bindParam(':signin_5b', $signin_5b);
        $stmt->bindParam(':signin_6a', $signin_6a);
        $stmt->bindParam(':signin_6b', $signin_6b);
        $stmt->bindParam(':signin_7a', $signin_7a);
        $stmt->bindParam(':signin_7b', $signin_7b);
        $stmt->bindParam(':dokter_anestesi_signin', $formData['dokter_anestesi_signin']);
        $stmt->bindParam(':perawat_anestesi_signin', $formData['perawat_anestesi_signin']);
        $stmt->bindParam(':perawat_sirkuler_signin', $formData['perawat_sirkuler_signin']);
        
        // Bind parameters - Time Out
        $stmt->bindParam(':timeout_time', $formData['timeout_time']);
        $stmt->bindParam(':timeout_1', $timeout_1);
        $stmt->bindParam(':timeout_2a', $timeout_2a);
        $stmt->bindParam(':timeout_2b', $timeout_2b);
        $stmt->bindParam(':timeout_2c', $timeout_2c);
        $stmt->bindParam(':timeout_3', $timeout_3);
        $stmt->bindParam(':catatan_dokter_bedah', $formData['catatan_dokter_bedah']);
        $stmt->bindParam(':catatan_dokter_anestesi', $formData['catatan_dokter_anestesi']);
        $stmt->bindParam(':catatan_perawat', $formData['catatan_perawat']);
        $stmt->bindParam(':timeout_5a', $timeout_5a);
        $stmt->bindParam(':timeout_5b', $timeout_5b);
        $stmt->bindParam(':perawat_sirkuler_timeout', $formData['perawat_sirkuler_timeout']);
        
        // Bind parameters - Sign Out
        $stmt->bindParam(':signout_time', $formData['signout_time']);
        $stmt->bindParam(':signout_1a', $signout_1a);
        $stmt->bindParam(':signout_1b', $signout_1b);
        $stmt->bindParam(':signout_1c', $signout_1c);
        $stmt->bindParam(':signout_1d', $signout_1d);
        $stmt->bindParam(':signout_2', $signout_2);
        $stmt->bindParam(':tanggal_keluar', $formData['tanggal_keluar']);
        $stmt->bindParam(':tahun_keluar', $formData['tahun_keluar']);
        $stmt->bindParam(':perawat_sirkuler_signout', $formData['perawat_sirkuler_signout']);
        $stmt->bindParam(':dokter_anestesi_signout', $formData['dokter_anestesi_signout']);
        $stmt->bindParam(':operator_signout', $formData['operator_signout']);
        
        // Execute query
        if ($stmt->execute()) {
            $_SESSION['success'] = $existing ? 'Data berhasil diperbarui!' : 'Data berhasil disimpan!';
            header("Location: ../index.php?page=keselamatan-operasi&no_rawat={$formData['no_rawat']}&kode_paket={$formData['kode_paket']}&tanggal={$formData['tanggal']}&jam_mulai={$formData['jam_mulai']}");
        } else {
            $errorInfo = $stmt->errorInfo();
            $_SESSION['error'] = 'Gagal menyimpan data: ' . $errorInfo[2];
            header("Location: ../index.php?page=keselamatan-operasi&no_rawat={$formData['no_rawat']}&kode_paket={$formData['kode_paket']}&tanggal={$formData['tanggal']}&jam_mulai={$formData['jam_mulai']}");
        }
        
    } catch (Exception $e) {
        error_log("Error submitting keselamatan operasi: " . $e->getMessage());
        $_SESSION['error'] = 'Terjadi kesalahan: ' . $e->getMessage();
        header("Location: ../index.php?page=keselamatan-operasi&no_rawat={$formData['no_rawat']}&kode_paket={$formData['kode_paket']}&tanggal={$formData['tanggal']}&jam_mulai={$formData['jam_mulai']}");
    }
    exit;
} else {
    header("Location: ../index.php");
    exit;
}
?>
