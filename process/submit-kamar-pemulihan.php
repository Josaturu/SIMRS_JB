<?php
// Submit Form Kamar Pemulihan - Production Version
require_once '../config/database.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        if (!$db) {
            throw new Exception("Database connection failed");
        }
        
        // Ambil data form
        $formData = $_POST;
        
        // Helper function: convert checkbox array to tinyint
        function checkboxArrayToBool($array, $value) {
            return (isset($array) && is_array($array) && in_array($value, $array)) ? 1 : 0;
        }
        
        // ===== CHECK IF RECORD EXISTS =====
        $checkQuery = "SELECT id FROM tbl_anestesi_kamar_pemulihan 
                       WHERE no_rawat = :no_rawat 
                       AND kode_paket = :kode_paket 
                       AND tanggal = :tanggal 
                       AND jam_mulai = :jam_mulai";
        
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->bindParam(':no_rawat', $formData['no_rawat']);
        $checkStmt->bindParam(':kode_paket', $formData['kode_paket']);
        $checkStmt->bindParam(':tanggal', $formData['tanggal']);
        $checkStmt->bindParam(':jam_mulai', $formData['jam_mulai']);
        $checkStmt->execute();
        
        $existingRecord = $checkStmt->fetch(PDO::FETCH_ASSOC);
        $isUpdate = ($existingRecord !== false);
        
        // Get ID (use existing or generate new)
        if ($isUpdate) {
            $id = $existingRecord['id'];
        } else {
            // Generate UUID untuk ID baru
            $id = sprintf(
                '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0x0fff) | 0x4000,
                mt_rand(0, 0x3fff) | 0x8000,
                mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
            );
        }
        
        // Mapping Data Masuk & Kondisi Pasien (9 checkboxes)
        $jalan_nafas_bersih = checkboxArrayToBool($formData['jalanNafas'] ?? null, 'bersih_lapang');
        $pernapasan_spontan = checkboxArrayToBool($formData['pernapasan'] ?? null, 'spontan');
        $pernapasan_dibantu = checkboxArrayToBool($formData['pernapasan'] ?? null, 'dibantu');
        $spontan_adekuat = checkboxArrayToBool($formData['spontan'] ?? null, 'adekuat');
        $spontan_penyumbatan = checkboxArrayToBool($formData['spontan'] ?? null, 'penyumbatan');
        $spontan_alat = checkboxArrayToBool($formData['spontan'] ?? null, 'alat');
        $kesadaran_sadar = checkboxArrayToBool($formData['kesadaran'] ?? null, 'sadar_betul');
        $kesadaran_belum_sadar = checkboxArrayToBool($formData['kesadaran'] ?? null, 'belum_sadar');
        $kesadaran_tidur_dalam = checkboxArrayToBool($formData['kesadaran'] ?? null, 'tidur_dalam');
        
        // Untuk backward compatibility, simpan juga ke kolom lama (jika ada)
        $jalan_nafas_text = implode(', ', $formData['jalanNafas'] ?? []);
        $pernapasan_text = implode(', ', $formData['pernapasan'] ?? []);
        $kesadaran_text = implode(', ', $formData['kesadaran'] ?? []);
        
        // ===== PREPARE QUERY (INSERT or UPDATE) =====
        if ($isUpdate) {
            // UPDATE existing record
            $query = "UPDATE tbl_anestesi_kamar_pemulihan SET
                       jam_masuk = :jam_masuk, tgl_masuk = :tgl_masuk,
                       jalan_nafas = :jalan_nafas, pernapasan = :pernapasan, kesadaran = :kesadaran,
                       jalan_nafas_bersih = :jalan_nafas_bersih, pernapasan_spontan = :pernapasan_spontan, pernapasan_dibantu = :pernapasan_dibantu,
                       spontan_adekuat = :spontan_adekuat, spontan_penyumbatan = :spontan_penyumbatan, spontan_alat = :spontan_alat,
                       kesadaran_sadar = :kesadaran_sadar, kesadaran_belum_sadar = :kesadaran_belum_sadar, kesadaran_tidur_dalam = :kesadaran_tidur_dalam,
                       nadi_1 = :nadi_1, nadi_2 = :nadi_2, nadi_3 = :nadi_3,
                       sistol_1 = :sistol_1, sistol_2 = :sistol_2, sistol_3 = :sistol_3,
                       diastol_1 = :diastol_1, diastol_2 = :diastol_2, diastol_3 = :diastol_3,
                       respirasi_1 = :respirasi_1, respirasi_2 = :respirasi_2, respirasi_3 = :respirasi_3,
                       nyeri_1 = :nyeri_1, nyeri_2 = :nyeri_2, nyeri_3 = :nyeri_3,
                       pemantauan_setiap = :pemantauan_setiap, pemantauan_selama = :pemantauan_selama,
                       analgesia = :analgesia, anti_muntah = :anti_muntah, antibiotik = :antibiotik,
                       posisi_pasien = :posisi_pasien, obat_lain = :obat_lain, diet_nutrisi = :diet_nutrisi, lain_lain = :lain_lain,
                       jam_keluar = :jam_keluar, td_keluar = :td_keluar, n_keluar = :n_keluar, r_keluar = :r_keluar, s_keluar = :s_keluar, spo2_keluar = :spo2_keluar,
                       skrining_nyeri = :skrining_nyeri, tujuan_keluar = :tujuan_keluar, catatan_khusus = :catatan_khusus,
                       aldrete_score = :aldrete_score, bromage_score = :bromage_score, steward_score = :steward_score,
                       nama_penanggungjawab = :nama_penanggungjawab, perawat_menyerahkan = :perawat_menyerahkan, perawat_menerima = :perawat_menerima, dokter_anestesi = :dokter_anestesi
                      WHERE id = :id";
        } else {
            // INSERT new record
            $query = "INSERT INTO tbl_anestesi_kamar_pemulihan (
                   id, no_rawat, kode_paket, tanggal, jam_mulai,
                   jam_masuk, tgl_masuk, 
                   jalan_nafas, pernapasan, kesadaran,
                   jalan_nafas_bersih, pernapasan_spontan, pernapasan_dibantu,
                   spontan_adekuat, spontan_penyumbatan, spontan_alat,
                   kesadaran_sadar, kesadaran_belum_sadar, kesadaran_tidur_dalam,
                   nadi_1, nadi_2, nadi_3,
                   sistol_1, sistol_2, sistol_3,
                   diastol_1, diastol_2, diastol_3,
                   respirasi_1, respirasi_2, respirasi_3,
                   nyeri_1, nyeri_2, nyeri_3,
                   pemantauan_setiap, pemantauan_selama,
                   analgesia, anti_muntah, antibiotik,
                   posisi_pasien, obat_lain, diet_nutrisi, lain_lain,
                   jam_keluar, td_keluar, n_keluar, r_keluar, s_keluar, spo2_keluar,
                   skrining_nyeri, tujuan_keluar, catatan_khusus,
                   aldrete_score, bromage_score, steward_score,
                   nama_penanggungjawab, perawat_menyerahkan, perawat_menerima, dokter_anestesi
                  ) VALUES (
                   :id, :no_rawat, :kode_paket, :tanggal, :jam_mulai,
                   :jam_masuk, :tgl_masuk,
                   :jalan_nafas, :pernapasan, :kesadaran,
                   :jalan_nafas_bersih, :pernapasan_spontan, :pernapasan_dibantu,
                   :spontan_adekuat, :spontan_penyumbatan, :spontan_alat,
                   :kesadaran_sadar, :kesadaran_belum_sadar, :kesadaran_tidur_dalam,
                   :nadi_1, :nadi_2, :nadi_3,
                   :sistol_1, :sistol_2, :sistol_3,
                   :diastol_1, :diastol_2, :diastol_3,
                   :respirasi_1, :respirasi_2, :respirasi_3,
                   :nyeri_1, :nyeri_2, :nyeri_3,
                   :pemantauan_setiap, :pemantauan_selama,
                   :analgesia, :anti_muntah, :antibiotik,
                   :posisi_pasien, :obat_lain, :diet_nutrisi, :lain_lain,
                   :jam_keluar, :td_keluar, :n_keluar, :r_keluar, :s_keluar, :spo2_keluar,
                   :skrining_nyeri, :tujuan_keluar, :catatan_khusus,
                   :aldrete_score, :bromage_score, :steward_score,
                   :nama_penanggungjawab, :perawat_menyerahkan, :perawat_menerima, :dokter_anestesi
                  )";
        }
        
        $stmt = $db->prepare($query);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . print_r($db->errorInfo(), true));
        }
        
        // Bind parameters - Metadata
        $stmt->bindParam(':id', $id);
        
        // Bind no_rawat, kode_paket, tanggal, jam_mulai only for INSERT
        if (!$isUpdate) {
            $stmt->bindParam(':no_rawat', $formData['no_rawat']);
            $stmt->bindParam(':kode_paket', $formData['kode_paket']);
            $stmt->bindParam(':tanggal', $formData['tanggal']);
            $stmt->bindParam(':jam_mulai', $formData['jam_mulai']);
        }
        
        // Bind parameters - Data Masuk
        $stmt->bindParam(':jam_masuk', $formData['jamMasuk']);
        $stmt->bindParam(':tgl_masuk', $formData['tglMasuk']);
        
        // Bind parameters - Backward compatibility (kolom lama)
        $stmt->bindParam(':jalan_nafas', $jalan_nafas_text);
        $stmt->bindParam(':pernapasan', $pernapasan_text);
        $stmt->bindParam(':kesadaran', $kesadaran_text);
        
        // Bind parameters - Data Masuk & Kondisi (kolom baru)
        $stmt->bindParam(':jalan_nafas_bersih', $jalan_nafas_bersih);
        $stmt->bindParam(':pernapasan_spontan', $pernapasan_spontan);
        $stmt->bindParam(':pernapasan_dibantu', $pernapasan_dibantu);
        $stmt->bindParam(':spontan_adekuat', $spontan_adekuat);
        $stmt->bindParam(':spontan_penyumbatan', $spontan_penyumbatan);
        $stmt->bindParam(':spontan_alat', $spontan_alat);
        $stmt->bindParam(':kesadaran_sadar', $kesadaran_sadar);
        $stmt->bindParam(':kesadaran_belum_sadar', $kesadaran_belum_sadar);
        $stmt->bindParam(':kesadaran_tidur_dalam', $kesadaran_tidur_dalam);
        
        // Bind parameters - Vital Signs (15 kolom)
        $nadi_1 = $formData['nadi_1'] ?? null;
        $nadi_2 = $formData['nadi_2'] ?? null;
        $nadi_3 = $formData['nadi_3'] ?? null;
        $sistol_1 = $formData['sistol_1'] ?? null;
        $sistol_2 = $formData['sistol_2'] ?? null;
        $sistol_3 = $formData['sistol_3'] ?? null;
        $diastol_1 = $formData['diastol_1'] ?? null;
        $diastol_2 = $formData['diastol_2'] ?? null;
        $diastol_3 = $formData['diastol_3'] ?? null;
        $respirasi_1 = $formData['respirasi_1'] ?? null;
        $respirasi_2 = $formData['respirasi_2'] ?? null;
        $respirasi_3 = $formData['respirasi_3'] ?? null;
        $nyeri_1 = $formData['nyeri_1'] ?? null;
        $nyeri_2 = $formData['nyeri_2'] ?? null;
        $nyeri_3 = $formData['nyeri_3'] ?? null;
        
        $stmt->bindParam(':nadi_1', $nadi_1);
        $stmt->bindParam(':nadi_2', $nadi_2);
        $stmt->bindParam(':nadi_3', $nadi_3);
        $stmt->bindParam(':sistol_1', $sistol_1);
        $stmt->bindParam(':sistol_2', $sistol_2);
        $stmt->bindParam(':sistol_3', $sistol_3);
        $stmt->bindParam(':diastol_1', $diastol_1);
        $stmt->bindParam(':diastol_2', $diastol_2);
        $stmt->bindParam(':diastol_3', $diastol_3);
        $stmt->bindParam(':respirasi_1', $respirasi_1);
        $stmt->bindParam(':respirasi_2', $respirasi_2);
        $stmt->bindParam(':respirasi_3', $respirasi_3);
        $stmt->bindParam(':nyeri_1', $nyeri_1);
        $stmt->bindParam(':nyeri_2', $nyeri_2);
        $stmt->bindParam(':nyeri_3', $nyeri_3);
        
        // Bind parameters - Instruksi Pasca Sedasi
        $stmt->bindParam(':pemantauan_setiap', $formData['pemantauan_setiap']);
        $stmt->bindParam(':pemantauan_selama', $formData['pemantauan_selama']);
        $stmt->bindParam(':analgesia', $formData['analgesia']);
        $stmt->bindParam(':anti_muntah', $formData['anti_muntah']);
        $stmt->bindParam(':antibiotik', $formData['antibiotik']);
        $stmt->bindParam(':posisi_pasien', $formData['posisi_pasien']);
        $stmt->bindParam(':obat_lain', $formData['obat_lain']);
        $stmt->bindParam(':diet_nutrisi', $formData['diet_nutrisi']);
        $stmt->bindParam(':lain_lain', $formData['lain_lain']);
        
        // Bind parameters - Keluar Kamar Pulih
        $stmt->bindParam(':jam_keluar', $formData['jam_keluar']);
        $stmt->bindParam(':td_keluar', $formData['td_keluar']);
        $stmt->bindParam(':n_keluar', $formData['n_keluar']);
        $stmt->bindParam(':r_keluar', $formData['r_keluar']);
        $stmt->bindParam(':s_keluar', $formData['s_keluar']);
        $stmt->bindParam(':spo2_keluar', $formData['spo2_keluar']);
        $stmt->bindParam(':skrining_nyeri', $formData['skrining_nyeri']);
        $stmt->bindParam(':tujuan_keluar', $formData['tujuan_keluar']);
        $stmt->bindParam(':catatan_khusus', $formData['catatan_khusus']);
        
        // Bind parameters - Penilaian/Scoring
        $aldrete_score = $formData['aldrete_score'] ?? null;
        $bromage_score = $formData['bromage_score'] ?? null;
        $steward_score = $formData['steward_score'] ?? null;
        
        $stmt->bindParam(':aldrete_score', $aldrete_score);
        $stmt->bindParam(':bromage_score', $bromage_score);
        $stmt->bindParam(':steward_score', $steward_score);
        
        // Bind parameters - Serah Terima
        $stmt->bindParam(':nama_penanggungjawab', $formData['nama_penanggungjawab']);
        $stmt->bindParam(':perawat_menyerahkan', $formData['perawat_menyerahkan']);
        $stmt->bindParam(':perawat_menerima', $formData['perawat_menerima']);
        $stmt->bindParam(':dokter_anestesi', $formData['dokter_anestesi']);
        
        // Execute query
        if ($stmt->execute()) {
            // Redirect back to form with success status (like form keselamatan)
            $action = $isUpdate ? 'updated' : 'saved';
            header("Location: ../index.php?page=kamar-pemulihan&no_rawat={$formData['no_rawat']}&kode_paket={$formData['kode_paket']}&tanggal={$formData['tanggal']}&jam_mulai={$formData['jam_mulai']}&status=sukses&action={$action}");
        } else {
            $errorInfo = $stmt->errorInfo();
            header("Location: ../index.php?page=kamar-pemulihan&no_rawat={$formData['no_rawat']}&kode_paket={$formData['kode_paket']}&tanggal={$formData['tanggal']}&jam_mulai={$formData['jam_mulai']}&status=gagal&error=" . urlencode($errorInfo[2]));
        }
        
    } catch (Exception $e) {
        error_log("Error submitting kamar pemulihan: " . $e->getMessage());
        // Redirect back to form with error and parameters
        $params = http_build_query([
            'page' => 'kamar-pemulihan',
            'no_rawat' => $formData['no_rawat'] ?? '',
            'kode_paket' => $formData['kode_paket'] ?? '',
            'tanggal' => $formData['tanggal'] ?? '',
            'jam_mulai' => $formData['jam_mulai'] ?? '',
            'status' => 'error',
            'msg' => $e->getMessage()
        ]);
        header("Location: ../index.php?{$params}");
    }
    exit;
} else {
    header("Location: ../index.php");
    exit;
}
?>
