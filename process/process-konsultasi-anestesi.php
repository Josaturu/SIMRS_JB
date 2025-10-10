<?php
include_once '../config/database.php';
include_once '../models/formmodel.php';

if ($_POST) {
    $database = new Database();
    $db = $database->getConnection();
    
    $formModel = new FormModel($db);
    
    // Generate UUID
    $id = uniqid();
    
    // Prepare data untuk bagian utama
    $data = [
        'id' => $id,
        'no_rawat' => $_POST['no_rawat'],
        'kode_paket' => $_POST['kode_paket'],
        'tanggal' => $_POST['tanggal'],
        'jam_mulai' => $_POST['jam_mulai'],
        'tinggi_badan' => $_POST['tinggiBadan'],
        'berat_badan' => $_POST['beratBadan'],
        'diagnosa_pra_operasi' => $_POST['diagnosaPraOperasi'],
        'rencana_operasi' => $_POST['rencanaTindakanOperasi'],
        'kondisi_khusus' => $_POST['kondisiKhusus'],
        'tanggal_dibuat' => $_POST['tanggalDibuat']
    ];
    
    // Kumpulkan semua data form untuk disimpan sebagai JSON di jawaban_konsul
    $jawaban_konsul = [
        'informasi_pasien' => [
            'ruang' => $_POST['ruang'],
            'dokter' => $_POST['dokter']
        ],
        'data_pasien' => [
            'tanggal_konsul' => $_POST['tanggalKonsul'],
            'jam' => $_POST['jam'],
            'jenis_diagnosa' => $_POST['jenisDiagnosa']
        ],
        'anamnesa' => [
            'jam_visit' => $_POST['jamVisit'],
            'menikah' => $_POST['menikah'],
            'jenis_kelamin' => $_POST['jenis_kelamin'],
            'merokok' => $_POST['merokok'],
            'alkohol' => $_POST['alkohol'],
            'pengobatan' => $_POST['pengobatan'],
            'alergi_obat' => $_POST['daftarAlergiObat'],
            'has_alergi_obat' => $_POST['has_alergi_obat'],
            'alergi_makanan' => $_POST['alergi_makanan'],
            'alergi_lateks' => $_POST['alergi_lateks'],
            'tidak_alergi' => $_POST['tidakAlergi'],
            'komunikasi' => $_POST['komunikasi'],
            'komunikasi_lainnya' => $_POST['komunikasiLainnya']
        ],
        'riwayat_penyakit' => [
            'asma' => $_POST['asma'],
            'hepatitis' => $_POST['hepatitis'],
            'sesak_nafas' => $_POST['sesak_nafas'],
            'pingsan' => $_POST['pingsan'],
            'sumbatan_jalan_nafas' => $_POST['sumbatan_jalan_nafas'],
            'diabetes' => $_POST['diabetes'],
            'tidur_mengorok' => $_POST['tidur_mengorok'],
            'anemia' => $_POST['anemia'],
            'serangan_jantung' => $_POST['serangan_jantung'],
            'sakit_maag' => $_POST['sakit_maag'],
            'hipertensi' => $_POST['hipertensi'],
            'pendarahan' => $_POST['pendarahan'],
            'stroke' => $_POST['stroke'],
            'pembekuan_darah' => $_POST['pembekuan_darah'],
            'kejang' => $_POST['kejang'],
            'penyakit_berat_lainnya' => $_POST['penyakit_berat_lainnya'],
            'penjelasan_penyakit' => $_POST['penjelasanPenyakit']
        ],
        'pemeriksaan_dokter' => [
            'jumlah_kehamilan' => $_POST['jumlahKehamilan'],
            'jumlah_anak' => $_POST['jumlahAnak'],
            'kesadaran' => $_POST['kesadaran'],
            'tb' => $_POST['tb'],
            'bb' => $_POST['bb'],
            'td' => $_POST['td'],
            'nadi' => $_POST['nadi'],
            'rr' => $_POST['rr'],
            'suhu' => $_POST['suhu'],
            'skrining_nyeri' => $_POST['skrining_nyeri'],
            'jalan_nafas' => $_POST['jalan_nafas'],
            'gerakan_leher' => $_POST['gerakan_leher'],
            'gerakan_leher_abnormal' => $_POST['gerakanLeherAbnormal'],
            'paru_paru' => $_POST['paruParu'],
            'jantung' => $_POST['jantung'],
            'abdomen' => $_POST['abdomen'],
            'ekstrimitas' => $_POST['ekstrimitas'],
            'neurologi' => $_POST['neurologi'],
            'lain_lain' => $_POST['lainLain'],
            'hb_ht_al_at' => $_POST['hbHtAlAt'],
            'na_k_cl' => $_POST['naKCl'],
            'ureum' => $_POST['ureum'],
            'ct_bt' => $_POST['ctBt'],
            'kreatin' => $_POST['kreatin'],
            'ekg' => $_POST['ekg'],
            'ro_dada' => $_POST['roDada'],
            'echo' => $_POST['echo'],
            'lain_lain_pemeriksaan' => $_POST['lainLainPemeriksaan'],
            'asa' => $_POST['asa'],
            'emergency' => $_POST['emergency'],
            'diagnosis_lain' => $_POST['diagnosisLain'],
            'anestesi_umum' => $_POST['anestesi_umum'],
            'regional' => $_POST['regional'],
            'combined' => $_POST['combined'],
            'sedasi' => $_POST['sedasi'],
            'saran' => $_POST['saran'],
            'puasa_mulai_jam' => $_POST['puasaMulaiJam'],
            'puasa_mulai_tanggal' => $_POST['puasaMulaiTanggal'],
            'rencana_tiba_jam' => $_POST['rencanaTibaJam'],
            'rencana_tiba_tanggal' => $_POST['rencanaTibaTanggal'],
            'rencana_operasi_jam' => $_POST['rencanaOperasiJam'],
            'rencana_operasi_tanggal' => $_POST['rencanaOperasiTanggal']
        ],
        'lain_lain' => [
            'gigi_palsu' => $_POST['gigi_palsu'],
            'makan_terakhir' => $_POST['makanTerakhir'],
            'riwayat_operasi' => $_POST['riwayatOperasi'],
            'jenis_anestesi_sebelumnya' => $_POST['jenisAnestesi'],
            'terakhir_periksa' => $_POST['terakhirPeriksa'],
            'tempat_periksa_terakhir' => $_POST['tempatPeriksaTerakhir'],
            'penyakit_gangguan' => $_POST['penyakitGangguan']
        ]
    ];
    
    $data['jawaban_konsul'] = json_encode($jawaban_konsul, JSON_UNESCAPED_UNICODE);
    
    if ($formModel->createKonsultasiAnestesi($data)) {
        header("Location: ../views/detail-pasien.php?no_rawat=" . $_POST['no_rawat'] . "&success=1");
        exit;
    } else {
        header("Location: ../views/form-konsultasi-anestesi.php?no_rawat=" . $_POST['no_rawat'] . "&kode_paket=" . $_POST['kode_paket'] . "&tanggal=" . $_POST['tanggal'] . "&jam_mulai=" . $_POST['jam_mulai'] . "&error=1");
        exit;
    }
}
?>