<?php
session_start();
include_once '../config/database.php';

if ($_POST) {
    $database = new Database();
    $db = $database->getConnection();
    
    // Cek apakah ini INSERT atau UPDATE (berdasarkan ID)
    $id = !empty($_POST['id']) ? $_POST['id'] : null;
    $is_update = !empty($_POST['id']);
    
    // Parameter utama (wajib untuk UNIQUE constraint)
    $no_rawat = $_POST['no_rawat'];
    $kode_paket = $_POST['kode_paket'];
    $tanggal = $_POST['tanggal'];
    $jam_mulai = $_POST['jam_mulai'];
    
    // Semua parameter lainnya
    $ruang_perawatan = $_POST['ruang'] ?? '';
    $dokter_merawat = $_POST['dokter'] ?? '';
    $tanggal_konsul = $_POST['tanggalKonsul'] ?? null;
    $jam_konsul = $_POST['jam'] ?? null;
    $tinggi_badan = $_POST['tinggiBadan'] ?? null;
    $berat_badan = $_POST['beratBadan'] ?? null;
    $diagnosa_pra_operasi = $_POST['diagnosaPraOperasi'] ?? '';
    $jenis_diagnosa = $_POST['jenisDiagnosa'] ?? 'Elektif'; // Cito atau Elektif
    $rencana_tindakan_operasi = $_POST['rencanaTindakanOperasi'] ?? '';
    $kondisi_khusus = $_POST['kondisiKhusus'] ?? '';
    $tanggal_dibuat = $_POST['tanggalDibuat'] ?? null;
    
    // Anamnesa
    $jam_visit = $_POST['jamVisit'] ?? null;
    $menikah = $_POST['menikah'] ?? null;
    $jenis_kelamin = $_POST['jenis_kelamin'] ?? null;
    $merokok = $_POST['merokok'] ?? null;
    $alkohol = $_POST['alkohol'] ?? null;
    
    // Debug log
    error_log("DEBUG - jenis_kelamin: " . ($jenis_kelamin ?? 'NULL'));
    error_log("DEBUG - merokok: " . ($merokok ?? 'NULL'));
    error_log("DEBUG - alkohol: " . ($alkohol ?? 'NULL'));
    $has_pengobatan = $_POST['has_pengobatan'] ?? null;
    $pengobatan = $_POST['pengobatan'] ?? '';
    $daftar_alergi_obat = $_POST['daftarAlergiObat'] ?? '';
    $has_alergi_obat = $_POST['has_alergi_obat'] ?? null;
    $alergi_makanan = $_POST['alergi_makanan'] ?? null;
    $alergi_lateks = $_POST['alergi_lateks'] ?? null;
    $tidak_alergi = $_POST['tidakAlergi'] ?? '';
    $komunikasi = $_POST['komunikasi'] ?? 'Bahasa Indonesia';
    $komunikasi_lainnya = $_POST['komunikasiLainnya'] ?? '';
    
    // Riwayat Penyakit (16 penyakit)
    $asma = $_POST['asma'] ?? null;
    $hepatitis = $_POST['hepatitis'] ?? null;
    $sesak_nafas = $_POST['sesak_nafas'] ?? null;
    $pingsan = $_POST['pingsan'] ?? null;
    $sumbatan_jalan_nafas = $_POST['sumbatan_jalan_nafas'] ?? null;
    $diabetes = $_POST['diabetes'] ?? null;
    $tidur_mengorok = $_POST['tidur_mengorok'] ?? null;
    $anemia = $_POST['anemia'] ?? null;
    $serangan_jantung = $_POST['serangan_jantung'] ?? null;
    $sakit_maag = $_POST['sakit_maag'] ?? null;
    $hipertensi = $_POST['hipertensi'] ?? null;
    $pendarahan = $_POST['pendarahan'] ?? null;
    $stroke = $_POST['stroke'] ?? null;
    $pembekuan_darah = $_POST['pembekuan_darah'] ?? null;
    $kejang = $_POST['kejang'] ?? null;
    $penyakit_berat_lainnya = $_POST['penyakit_berat_lainnya'] ?? null;
    
    $penyakit_terpilih = $_POST['penjelasanPenyakit'] ?? '';
    $gigi_palsu = $_POST['gigi_palsu'] ?? null;
    $makan_terakhir = $_POST['makanTerakhir'] ?? null;
    $riwayat_operasi = $_POST['riwayatOperasi'] ?? '';
    $jenis_anestesi = $_POST['jenisAnestesi'] ?? '';
    $terakhir_periksa = $_POST['terakhirPeriksa'] ?? null;
    $tempat_periksa_terakhir = $_POST['tempatPeriksaTerakhir'] ?? '';
    $penyakit_gangguan = $_POST['penyakitGangguan'] ?? '';
    
    // Pemeriksaan Dokter
    $jumlah_kehamilan = $_POST['jumlahKehamilan'] ?? null;
    $jumlah_anak = $_POST['jumlahAnak'] ?? null;
    $menyusui = $_POST['menyusui'] ?? null;
    $kesadaran = $_POST['kesadaran'] ?? '';
    $tb = $_POST['tb'] ?? null;
    $bb = $_POST['bb'] ?? null;
    $td = $_POST['td'] ?? '';
    $nadi = $_POST['nadi'] ?? null;
    $rr = $_POST['rr'] ?? null;
    $suhu = $_POST['suhu'] ?? null;
    $skrining_nyeri = $_POST['skrining_nyeri'] ?? null;
    $jalan_nafas = $_POST['jalan_nafas'] ?? null;
    $gerakan_leher = $_POST['gerakan_leher'] ?? null;
    $gerakan_leher_keterangan = $_POST['gerakanLeherAbnormal'] ?? '';
    $paru_paru = $_POST['paruParu'] ?? '';
    $jantung = $_POST['jantung'] ?? '';
    $abdomen = $_POST['abdomen'] ?? '';
    $ekstrimitas = $_POST['ekstrimitas'] ?? '';
    $neurologi = $_POST['neurologi'] ?? '';
    $lain_lain = $_POST['lainLain'] ?? '';
    
    // Pemeriksaan Penunjang
    $hb_ht_al_at = $_POST['hbHtAlAt'] ?? '';
    $na_k_cl = $_POST['naKCl'] ?? '';
    $ureum = $_POST['ureum'] ?? '';
    $ct_bt = $_POST['ctBt'] ?? '';
    $kreatin = $_POST['kreatin'] ?? '';
    $ekg = $_POST['ekg'] ?? '';
    $ro_dada = $_POST['roDada'] ?? '';
    $echo = $_POST['echo'] ?? '';
    $lain_lain_pemeriksaan = $_POST['lainLainPemeriksaan'] ?? '';
    
    // Diagnosis & Rekomendasi
    $asa_status = $_POST['asa'] ?? null;
    $emergency = $_POST['emergency'] ?? null;
    $rekomendasi_anestesi = $_POST['diagnosisLain'] ?? '';
    
    // Debug log emergency
    error_log("DEBUG - emergency: " . ($emergency ?? 'NULL'));
    
    // Jenis Anestesi yang Dipilih
    $anestesi_umum = $_POST['anestesi_umum'] ?? null;
    $regional_anestesi = $_POST['regional'] ?? null;
    $kombinasi_anestesi = $_POST['combined'] ?? null;
    $sedasi = $_POST['sedasi'] ?? null;
    
    $saran = $_POST['saran'] ?? '';
    $puasa_mulai_jam = $_POST['puasaMulaiJam'] ?? null;
    $puasa_mulai_tanggal = $_POST['puasaMulaiTanggal'] ?? null;
    $rencana_tiba_jam = $_POST['rencanaTibaJam'] ?? null;
    $rencana_tiba_tanggal = $_POST['rencanaTibaTanggal'] ?? null;
    $rencana_operasi_jam = $_POST['rencanaOperasiJam'] ?? null;
    $rencana_operasi_tanggal = $_POST['rencanaOperasiTanggal'] ?? null;
    
    // Query dengan INSERT ... ON DUPLICATE KEY UPDATE
    $query = "INSERT INTO tbl_anestesi_konsultasi_anestesi 
              (no_rawat, kode_paket, tanggal, jam_mulai, ruang_perawatan, dokter_merawat,
               tanggal_konsul, jam_konsul, tinggi_badan, berat_badan, diagnosa_pra_operasi, jenis_diagnosa,
               rencana_tindakan_operasi, kondisi_khusus, tanggal_dibuat, jam_visit, menikah,
               jenis_kelamin, merokok, alkohol, has_pengobatan, pengobatan, daftar_alergi_obat, has_alergi_obat,
               alergi_makanan, alergi_lateks, tidak_alergi, komunikasi, komunikasi_lainnya,
               asma, hepatitis, sesak_nafas, pingsan, sumbatan_jalan_nafas, diabetes,
               tidur_mengorok, anemia, serangan_jantung, sakit_maag, hipertensi, pendarahan,
               stroke, pembekuan_darah, kejang, penyakit_berat_lainnya, penyakit_terpilih,
               gigi_palsu, makan_terakhir, riwayat_operasi, jenis_anestesi, terakhir_periksa,
               tempat_periksa_terakhir, penyakit_gangguan, jumlah_kehamilan, jumlah_anak,
               menyusui, kesadaran, tb, bb, td, nadi, rr, suhu, skrining_nyeri, jalan_nafas,
               gerakan_leher, gerakan_leher_keterangan, paru_paru, jantung, abdomen, ekstrimitas,
               neurologi, lain_lain, hb_ht_al_at, na_k_cl, ureum, ct_bt, kreatin, ekg, ro_dada,
               echo, lain_lain_pemeriksaan, asa_status, emergency, rekomendasi_anestesi,
               anestesi_umum, regional_anestesi, kombinasi_anestesi, sedasi, saran,
               puasa_mulai_jam, puasa_mulai_tanggal, rencana_tiba_jam, rencana_tiba_tanggal,
               rencana_operasi_jam, rencana_operasi_tanggal)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                      ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                      ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                      ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
              ON DUPLICATE KEY UPDATE
                ruang_perawatan = VALUES(ruang_perawatan),
                dokter_merawat = VALUES(dokter_merawat),
                tanggal_konsul = VALUES(tanggal_konsul),
                jam_konsul = VALUES(jam_konsul),
                tinggi_badan = VALUES(tinggi_badan),
                berat_badan = VALUES(berat_badan),
                diagnosa_pra_operasi = VALUES(diagnosa_pra_operasi),
                jenis_diagnosa = VALUES(jenis_diagnosa),
                rencana_tindakan_operasi = VALUES(rencana_tindakan_operasi),
                kondisi_khusus = VALUES(kondisi_khusus),
                tanggal_dibuat = VALUES(tanggal_dibuat),
                jam_visit = VALUES(jam_visit),
                menikah = VALUES(menikah),
                jenis_kelamin = VALUES(jenis_kelamin),
                merokok = VALUES(merokok),
                alkohol = VALUES(alkohol),
                has_pengobatan = VALUES(has_pengobatan),
                pengobatan = VALUES(pengobatan),
                daftar_alergi_obat = VALUES(daftar_alergi_obat),
                has_alergi_obat = VALUES(has_alergi_obat),
                alergi_makanan = VALUES(alergi_makanan),
                alergi_lateks = VALUES(alergi_lateks),
                tidak_alergi = VALUES(tidak_alergi),
                komunikasi = VALUES(komunikasi),
                komunikasi_lainnya = VALUES(komunikasi_lainnya),
                asma = VALUES(asma),
                hepatitis = VALUES(hepatitis),
                sesak_nafas = VALUES(sesak_nafas),
                pingsan = VALUES(pingsan),
                sumbatan_jalan_nafas = VALUES(sumbatan_jalan_nafas),
                diabetes = VALUES(diabetes),
                tidur_mengorok = VALUES(tidur_mengorok),
                anemia = VALUES(anemia),
                serangan_jantung = VALUES(serangan_jantung),
                sakit_maag = VALUES(sakit_maag),
                hipertensi = VALUES(hipertensi),
                pendarahan = VALUES(pendarahan),
                stroke = VALUES(stroke),
                pembekuan_darah = VALUES(pembekuan_darah),
                kejang = VALUES(kejang),
                penyakit_berat_lainnya = VALUES(penyakit_berat_lainnya),
                penyakit_terpilih = VALUES(penyakit_terpilih),
                gigi_palsu = VALUES(gigi_palsu),
                makan_terakhir = VALUES(makan_terakhir),
                riwayat_operasi = VALUES(riwayat_operasi),
                jenis_anestesi = VALUES(jenis_anestesi),
                terakhir_periksa = VALUES(terakhir_periksa),
                tempat_periksa_terakhir = VALUES(tempat_periksa_terakhir),
                penyakit_gangguan = VALUES(penyakit_gangguan),
                jumlah_kehamilan = VALUES(jumlah_kehamilan),
                jumlah_anak = VALUES(jumlah_anak),
                menyusui = VALUES(menyusui),
                kesadaran = VALUES(kesadaran),
                tb = VALUES(tb),
                bb = VALUES(bb),
                td = VALUES(td),
                nadi = VALUES(nadi),
                rr = VALUES(rr),
                suhu = VALUES(suhu),
                skrining_nyeri = VALUES(skrining_nyeri),
                jalan_nafas = VALUES(jalan_nafas),
                gerakan_leher = VALUES(gerakan_leher),
                gerakan_leher_keterangan = VALUES(gerakan_leher_keterangan),
                paru_paru = VALUES(paru_paru),
                jantung = VALUES(jantung),
                abdomen = VALUES(abdomen),
                ekstrimitas = VALUES(ekstrimitas),
                neurologi = VALUES(neurologi),
                lain_lain = VALUES(lain_lain),
                hb_ht_al_at = VALUES(hb_ht_al_at),
                na_k_cl = VALUES(na_k_cl),
                ureum = VALUES(ureum),
                ct_bt = VALUES(ct_bt),
                kreatin = VALUES(kreatin),
                ekg = VALUES(ekg),
                ro_dada = VALUES(ro_dada),
                echo = VALUES(echo),
                lain_lain_pemeriksaan = VALUES(lain_lain_pemeriksaan),
                asa_status = VALUES(asa_status),
                emergency = VALUES(emergency),
                rekomendasi_anestesi = VALUES(rekomendasi_anestesi),
                anestesi_umum = VALUES(anestesi_umum),
                regional_anestesi = VALUES(regional_anestesi),
                kombinasi_anestesi = VALUES(kombinasi_anestesi),
                sedasi = VALUES(sedasi),
                saran = VALUES(saran),
                puasa_mulai_jam = VALUES(puasa_mulai_jam),
                puasa_mulai_tanggal = VALUES(puasa_mulai_tanggal),
                rencana_tiba_jam = VALUES(rencana_tiba_jam),
                rencana_tiba_tanggal = VALUES(rencana_tiba_tanggal),
                rencana_operasi_jam = VALUES(rencana_operasi_jam),
                rencana_operasi_tanggal = VALUES(rencana_operasi_tanggal)";
    
    $stmt = $db->prepare($query);
    
    // Debug: Log error jika ada
    try {
        // Execute dengan semua 96 parameter (termasuk jenis_diagnosa)
        $success = $stmt->execute([
        $no_rawat, $kode_paket, $tanggal, $jam_mulai, $ruang_perawatan, $dokter_merawat,
        $tanggal_konsul, $jam_konsul, $tinggi_badan, $berat_badan, $diagnosa_pra_operasi, $jenis_diagnosa,
        $rencana_tindakan_operasi, $kondisi_khusus, $tanggal_dibuat, $jam_visit, $menikah,
        $jenis_kelamin, $merokok, $alkohol, $has_pengobatan, $pengobatan, $daftar_alergi_obat, $has_alergi_obat,
        $alergi_makanan, $alergi_lateks, $tidak_alergi, $komunikasi, $komunikasi_lainnya,
        $asma, $hepatitis, $sesak_nafas, $pingsan, $sumbatan_jalan_nafas, $diabetes,
        $tidur_mengorok, $anemia, $serangan_jantung, $sakit_maag, $hipertensi, $pendarahan,
        $stroke, $pembekuan_darah, $kejang, $penyakit_berat_lainnya, $penyakit_terpilih,
        $gigi_palsu, $makan_terakhir, $riwayat_operasi, $jenis_anestesi, $terakhir_periksa,
        $tempat_periksa_terakhir, $penyakit_gangguan, $jumlah_kehamilan, $jumlah_anak,
        $menyusui, $kesadaran, $tb, $bb, $td, $nadi, $rr, $suhu, $skrining_nyeri, $jalan_nafas,
        $gerakan_leher, $gerakan_leher_keterangan, $paru_paru, $jantung, $abdomen, $ekstrimitas,
        $neurologi, $lain_lain, $hb_ht_al_at, $na_k_cl, $ureum, $ct_bt, $kreatin, $ekg, $ro_dada,
        $echo, $lain_lain_pemeriksaan, $asa_status, $emergency, $rekomendasi_anestesi,
        $anestesi_umum, $regional_anestesi, $kombinasi_anestesi, $sedasi, $saran,
        $puasa_mulai_jam, $puasa_mulai_tanggal, $rencana_tiba_jam, $rencana_tiba_tanggal,
        $rencana_operasi_jam, $rencana_operasi_tanggal
    ]);
        
        // Cek apakah ini update atau insert
        $is_update = ($stmt->rowCount() > 0 && $db->lastInsertId() == 0);
        
        if ($success) {
            $_SESSION['success'] = $is_update ? 'Data konsultasi berhasil diperbarui!' : 'Data konsultasi berhasil disimpan!';
            header("Location: ../index.php?page=konsultasi-anestesi&no_rawat=" . urlencode($no_rawat) . "&kode_paket=" . urlencode($kode_paket) . "&tanggal=" . urlencode($tanggal) . "&jam_mulai=" . urlencode($jam_mulai));
            exit;
        } else {
            $_SESSION['error'] = 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi.';
            header("Location: ../index.php?page=konsultasi-anestesi&no_rawat=" . urlencode($no_rawat) . "&kode_paket=" . urlencode($kode_paket) . "&tanggal=" . urlencode($tanggal) . "&jam_mulai=" . urlencode($jam_mulai));
            exit;
        }
    } catch (PDOException $e) {
        // Log error untuk debugging
        error_log("Error konsultasi anestesi: " . $e->getMessage());
        error_log("Error Code: " . $e->getCode());
        
        // Cek jika error adalah parameter mismatch
        if ($e->getCode() == 'HY093') {
            // Hitung jumlah placeholder
            $placeholder_count = substr_count($query, '?');
            error_log("Placeholder count in query: " . $placeholder_count);
            error_log("Parameter count in execute: 96");
            
            $_SESSION['error'] = 'Parameter mismatch! Query memiliki ' . $placeholder_count . ' placeholder, tapi execute() memiliki 96 parameter. Periksa query INSERT.';
        } else {
            $_SESSION['error'] = 'Terjadi kesalahan database: ' . $e->getMessage();
        }
        
        header("Location: ../index.php?page=konsultasi-anestesi&no_rawat=" . urlencode($no_rawat) . "&kode_paket=" . urlencode($kode_paket) . "&tanggal=" . urlencode($tanggal) . "&jam_mulai=" . urlencode($jam_mulai));
        exit;
    }
}
?>
