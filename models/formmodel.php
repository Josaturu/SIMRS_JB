<?php
class FormModel {
    private $conn;
    private $table_persiapan = "tbl_anestesi_persiapan_operasi";
    private $table_keselamatan = "tbl_anestesi_checklist_keselamatan";
    private $table_pemulihan = "tbl_anestesi_kamar_pemulihan";
    private $table_vital_pemulihan = "tbl_anestesi_vital_pemulihan";
    private $table_catatan_anestesi = "tbl_anestesi_catatan_anestesi";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Method untuk generate kode_paket otomatis
    public function generateKodePaket($no_rawat, $nama_pasien = '') {
        $tahun = date('Y');
        $bulan = date('m');
        $hari = date('d');

        // Ambil nomor urut terakhir untuk hari ini
        $query = "SELECT COUNT(*) as total FROM booking_operasi WHERE DATE(tanggal) = CURDATE()";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $nomor_urut = $result['total'] + 1;

        // Format: TAHUNBULANHARI-NAMA/INISIAL-NOURUT
        $nama_kode = '';
        if (!empty($nama_pasien)) {
            $nama_array = array_filter(explode(' ', $nama_pasien));
            $nama_array = array_values($nama_array); // reindex
            $jml = count($nama_array);
            if ($jml <= 2) {
                $nama_kode = implode(' ', $nama_array);
            } else {
                $nama_kode = $nama_array[0] . ' ' . $nama_array[1];
                // Inisial dari kata ke-3 dst
                for ($i = 2; $i < $jml; $i++) {
                    $nama_kode .= ' ' . strtoupper(substr($nama_array[$i], 0, 1));
                }
            }
        } else {
            $nama_kode = 'PST'; // Default jika nama tidak ada
        }

        $kode_paket = $tahun . $bulan . $hari . '-' . $nama_kode . '-' . str_pad($nomor_urut, 3, '0', STR_PAD_LEFT);
        return $kode_paket;
    }

    // Method untuk membuat booking operasi baru
    public function createBookingOperasi($data) {
        $query = "INSERT INTO booking_operasi 
                 SET no_rawat=:no_rawat, kode_paket=:kode_paket, tanggal=:tanggal, 
                     jam_mulai=:jam_mulai, jam_selesai=:jam_selesai, status=:status,
                     kd_dokter=:kd_dokter, kd_ruang_ok=:kd_ruang_ok";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":no_rawat", $data['no_rawat']);
        $stmt->bindParam(":kode_paket", $data['kode_paket']);
        $stmt->bindParam(":tanggal", $data['tanggal']);
        $stmt->bindParam(":jam_mulai", $data['jam_mulai']);
        $stmt->bindParam(":jam_selesai", $data['jam_selesai']);
        $stmt->bindParam(":status", $data['status']);
        $stmt->bindParam(":kd_dokter", $data['kd_dokter']);
        $stmt->bindParam(":kd_ruang_ok", $data['kd_ruang_ok']);
        
        return $stmt->execute();
    }

    // Method untuk Checklist Persiapan Operasi
    public function createChecklistPersiapan($data) {
        $query = "INSERT INTO " . $this->table_persiapan . " 
                 SET id=:id, no_rawat=:no_rawat, kode_paket=:kode_paket, 
                     tanggal=:tanggal, jam_mulai=:jam_mulai, tgl_operasi=:tgl_operasi,
                     macam_operasi=:macam_operasi, tinggi_badan=:tinggi_badan,
                     berat_badan=:berat_badan, gol_darah=:gol_darah, 
                     riwayat_alergi=:riwayat_alergi, hasil_pemeriksaan=:hasil_pemeriksaan";
        
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bindParam(":id", $data['id']);
        $stmt->bindParam(":no_rawat", $data['no_rawat']);
        $stmt->bindParam(":kode_paket", $data['kode_paket']);
        $stmt->bindParam(":tanggal", $data['tanggal']);
        $stmt->bindParam(":jam_mulai", $data['jam_mulai']);
        $stmt->bindParam(":tgl_operasi", $data['tgl_operasi']);
        $stmt->bindParam(":macam_operasi", $data['macam_operasi']);
        $stmt->bindParam(":tinggi_badan", $data['tinggi_badan']);
        $stmt->bindParam(":berat_badan", $data['berat_badan']);
        $stmt->bindParam(":gol_darah", $data['gol_darah']);
        $stmt->bindParam(":riwayat_alergi", $data['riwayat_alergi']);
        $stmt->bindParam(":hasil_pemeriksaan", $data['hasil_pemeriksaan']);
        
        return $stmt->execute();
    }

    // Method untuk Checklist Keselamatan Operasi
    public function createChecklistKeselamatan($data) {
        $query = "INSERT INTO " . $this->table_keselamatan . " 
                 SET id=:id, no_rawat=:no_rawat, kode_paket=:kode_paket,
                     tanggal=:tanggal, jam_mulai=:jam_mulai, operasi=:operasi,
                     tanggal_tindakan=:tanggal_tindakan, signin=:signin,
                     timeout=:timeout, signout=:signout";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":id", $data['id']);
        $stmt->bindParam(":no_rawat", $data['no_rawat']);
        $stmt->bindParam(":kode_paket", $data['kode_paket']);
        $stmt->bindParam(":tanggal", $data['tanggal']);
        $stmt->bindParam(":jam_mulai", $data['jam_mulai']);
        $stmt->bindParam(":operasi", $data['operasi']);
        $stmt->bindParam(":tanggal_tindakan", $data['tanggal_tindakan']);
        $stmt->bindParam(":signin", $data['signin']);
        $stmt->bindParam(":timeout", $data['timeout']);
        $stmt->bindParam(":signout", $data['signout']);
        
        return $stmt->execute();
    }

    // Method untuk Kamar Pemulihan
    public function createKamarPemulihan($data) {
        $query = "INSERT INTO " . $this->table_pemulihan . " 
                 SET id=:id, no_rawat=:no_rawat, kode_paket=:kode_paket,
                     tanggal=:tanggal, jam_mulai=:jam_mulai, jam_masuk=:jam_masuk,
                     tgl_masuk=:tgl_masuk, jalan_nafas=:jalan_nafas,
                     pernapasan=:pernapasan, kesadaran=:kesadaran, instruksi=:instruksi";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":id", $data['id']);
        $stmt->bindParam(":no_rawat", $data['no_rawat']);
        $stmt->bindParam(":kode_paket", $data['kode_paket']);
        $stmt->bindParam(":tanggal", $data['tanggal']);
        $stmt->bindParam(":jam_mulai", $data['jam_mulai']);
        $stmt->bindParam(":jam_masuk", $data['jam_masuk']);
        $stmt->bindParam(":tgl_masuk", $data['tgl_masuk']);
        $stmt->bindParam(":jalan_nafas", $data['jalan_nafas']);
        $stmt->bindParam(":pernapasan", $data['pernapasan']);
        $stmt->bindParam(":kesadaran", $data['kesadaran']);
        $stmt->bindParam(":instruksi", $data['instruksi']);
        
        return $stmt->execute();
    }

    // Method untuk Vital Sign Pemulihan
    public function createVitalPemulihan($data) {
        $query = "INSERT INTO " . $this->table_vital_pemulihan . " 
                 SET id=:id, no_rawat=:no_rawat, kode_paket=:kode_paket,
                     tanggal=:tanggal, jam_mulai=:jam_mulai, id_pemulihan=:id_pemulihan,
                     waktu_label=:waktu_label, nadi=:nadi, sistol=:sistol,
                     diastol=:diastol, respirasi=:respirasi, nyeri=:nyeri";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":id", $data['id']);
        $stmt->bindParam(":no_rawat", $data['no_rawat']);
        $stmt->bindParam(":kode_paket", $data['kode_paket']);
        $stmt->bindParam(":tanggal", $data['tanggal']);
        $stmt->bindParam(":jam_mulai", $data['jam_mulai']);
        $stmt->bindParam(":id_pemulihan", $data['id_pemulihan']);
        $stmt->bindParam(":waktu_label", $data['waktu_label']);
        $stmt->bindParam(":nadi", $data['nadi']);
        $stmt->bindParam(":sistol", $data['sistol']);
        $stmt->bindParam(":diastol", $data['diastol']);
        $stmt->bindParam(":respirasi", $data['respirasi']);
        $stmt->bindParam(":nyeri", $data['nyeri']);
        
        return $stmt->execute();
    }
    public function createCatatanAnestesi($data) {
        // Gunakan field yang sesuai dengan struktur tabel sebenarnya
        $query = "INSERT INTO " . $this->table_catatan_anestesi . " 
                 SET id=:id, no_rawat=:no_rawat, kode_paket=:kode_paket,
                     tanggal=:tanggal, jam_mulai=:jam_mulai, 
                     no_rm=:no_rm, nama=:nama, tgl_lahir=:tgl_lahir,
                     ruang_perawatan=:ruang_perawatan, dokter_merawat=:dokter_merawat,
                     dokter_anestesi=:dokter_anestesi, perawat_anestesi=:perawat_anestesi,
                     diagnosa_pra_bedah=:diagnosa_pra_bedah, nama_tindakan=:nama_tindakan,
                     diagnosa_pasca_bedah=:diagnosa_pasca_bedah, asessment_pra_anestesi=:asessment_pra_anestesi,
                     tanggal_anestesi=:tanggal_anestesi, pukul=:pukul,
                     dokter_bedah=:dokter_bedah, perawat_bedah=:perawat_bedah,
                     jenis_pembedahan=:jenis_pembedahan, bb=:bb, td=:td, suhu=:suhu,
                     respirasi=:respirasi, hb=:hb, tb=:tb, nadi=:nadi, gcs=:gcs,
                     golongan_darah=:golongan_darah, skrining_nyeri=:skrining_nyeri,
                     status_fisik_asa=:status_fisik_asa, penyulit_pra_anestesi=:penyulit_pra_anestesi,
                     jenis_anestesi=:jenis_anestesi, resiko=:resiko,
                     checklist_sebelum_induksi=:checklist_sebelum_induksi, teknik_anestesi=:teknik_anestesi,
                     infus_perifer=:infus_perifer, induksi=:induksi, jalan_nafas=:jalan_nafas, ventilasi=:ventilasi,
                     ventilator=:ventilator, ukuran_balon=:ukuran_balon, jenis_balon=:jenis_balon,
                     posisi_ett=:posisi_ett, lain_lain_balon=:lain_lain_balon,
                     lokasi_regional=:lokasi_regional, jarum_regional=:jarum_regional,
                     kateter_regional=:kateter_regional, obat_anestesi_lokal=:obat_anestesi_lokal,
                     hasil_regional=:hasil_regional, obat=:obat, cairan_infus=:cairan_infus,
                     cairan_output=:cairan_output, masalah_selama_anestesi=:masalah_selama_anestesi,
                     tindakan=:tindakan, keterangan=:keterangan,
                     perawat_menyerahkan=:perawat_menyerahkan, perawat_menerima=:perawat_menerima,
                     dokter_anestesi_ttd=:dokter_anestesi_ttd,
                     mulai_anestesi=:mulai_anestesi, selesai_anestesi=:selesai_anestesi,
                     mulai_pembedahan=:mulai_pembedahan, selesai_pembedahan=:selesai_pembedahan,
                     keterangan_waktu=:keterangan_waktu, induksi_pukul=:induksi_pukul,
                     pasien_siap_insisi=:pasien_siap_insisi, insisi_mulai_pukul=:insisi_mulai_pukul,
                     operasi_mulai_pukul=:operasi_mulai_pukul, ekstubasi_pukul=:ekstubasi_pukul,
                     pasien_keluar_ok=:pasien_keluar_ok";
        
        $stmt = $this->conn->prepare($query);
        
        // Bind semua parameter sesuai dengan field di database
        $stmt->bindParam(":id", $data['id']);
        $stmt->bindParam(":no_rawat", $data['no_rawat']);
        $stmt->bindParam(":kode_paket", $data['kode_paket']);
        $stmt->bindParam(":tanggal", $data['tanggal']);
        $stmt->bindParam(":jam_mulai", $data['jam_mulai']);
        $stmt->bindParam(":no_rm", $data['no_rm']);
        $stmt->bindParam(":nama", $data['nama']);
        $stmt->bindParam(":tgl_lahir", $data['tgl_lahir']);
        $stmt->bindParam(":ruang_perawatan", $data['ruang_perawatan']);
        $stmt->bindParam(":dokter_merawat", $data['dokter_merawat']);
        $stmt->bindParam(":dokter_anestesi", $data['dokter_anestesi']);
        $stmt->bindParam(":perawat_anestesi", $data['perawat_anestesi']);
        $stmt->bindParam(":diagnosa_pra_bedah", $data['diagnosa_pra_bedah']);
        $stmt->bindParam(":nama_tindakan", $data['nama_tindakan']);
        $stmt->bindParam(":diagnosa_pasca_bedah", $data['diagnosa_pasca_bedah']);
        $stmt->bindParam(":asessment_pra_anestesi", $data['asessment_pra_anestesi']);
        $stmt->bindParam(":tanggal_anestesi", $data['tanggal_anestesi']);
        $stmt->bindParam(":pukul", $data['pukul']);
        $stmt->bindParam(":dokter_bedah", $data['dokter_bedah']);
        $stmt->bindParam(":perawat_bedah", $data['perawat_bedah']);
        $stmt->bindParam(":jenis_pembedahan", $data['jenis_pembedahan']);
        $stmt->bindParam(":bb", $data['bb']);
        $stmt->bindParam(":td", $data['td']);
        $stmt->bindParam(":suhu", $data['suhu']);
        $stmt->bindParam(":respirasi", $data['respirasi']);
        $stmt->bindParam(":hb", $data['hb']);
        $stmt->bindParam(":tb", $data['tb']);
        $stmt->bindParam(":nadi", $data['nadi']);
        $stmt->bindParam(":gcs", $data['gcs']);
        $stmt->bindParam(":golongan_darah", $data['golongan_darah']);
        $stmt->bindParam(":skrining_nyeri", $data['skrining_nyeri']);
        $stmt->bindParam(":status_fisik_asa", $data['status_fisik_asa']);
        $stmt->bindParam(":penyulit_pra_anestesi", $data['penyulit_pra_anestesi']);
        $stmt->bindParam(":jenis_anestesi", $data['jenis_anestesi']);
        $stmt->bindParam(":resiko", $data['resiko']);
        $stmt->bindParam(":checklist_sebelum_induksi", $data['checklist_sebelum_induksi']);
        $stmt->bindParam(":teknik_anestesi", $data['teknik_anestesi']);
        $stmt->bindParam(":infus_perifer", $data['infus_perifer']);
        $stmt->bindParam(":induksi", $data['induksi']);
        $stmt->bindParam(":jalan_nafas", $data['jalan_nafas']);
        $stmt->bindParam(":ventilasi", $data['ventilasi']);
        $stmt->bindParam(":ventilator", $data['ventilator']);
        $stmt->bindParam(":ukuran_balon", $data['ukuran_balon']);
        $stmt->bindParam(":jenis_balon", $data['jenis_balon']);
        $stmt->bindParam(":posisi_ett", $data['posisi_ett']);
        $stmt->bindParam(":lain_lain_balon", $data['lain_lain_balon']);
        $stmt->bindParam(":lokasi_regional", $data['lokasi_regional']);
        $stmt->bindParam(":jarum_regional", $data['jarum_regional']);
        $stmt->bindParam(":kateter_regional", $data['kateter_regional']);
        $stmt->bindParam(":obat_anestesi_lokal", $data['obat_anestesi_lokal']);
        $stmt->bindParam(":hasil_regional", $data['hasil_regional']);
        $stmt->bindParam(":obat", $data['obat']);
        $stmt->bindParam(":cairan_infus", $data['cairan_infus']);
        $stmt->bindParam(":cairan_output", $data['cairan_output']);
        $stmt->bindParam(":masalah_selama_anestesi", $data['masalah_selama_anestesi']);
        $stmt->bindParam(":tindakan", $data['tindakan']);
        $stmt->bindParam(":keterangan", $data['keterangan']);
        $stmt->bindParam(":perawat_menyerahkan", $data['perawat_menyerahkan']);
        $stmt->bindParam(":perawat_menerima", $data['perawat_menerima']);
        $stmt->bindParam(":dokter_anestesi_ttd", $data['dokter_anestesi_ttd']);
        $stmt->bindParam(":mulai_anestesi", $data['mulai_anestesi']);
        $stmt->bindParam(":selesai_anestesi", $data['selesai_anestesi']);
        $stmt->bindParam(":mulai_pembedahan", $data['mulai_pembedahan']);
        $stmt->bindParam(":selesai_pembedahan", $data['selesai_pembedahan']);
        $stmt->bindParam(":keterangan_waktu", $data['keterangan_waktu']);
        $stmt->bindParam(":induksi_pukul", $data['induksi_pukul']);
        $stmt->bindParam(":pasien_siap_insisi", $data['pasien_siap_insisi']);
        $stmt->bindParam(":insisi_mulai_pukul", $data['insisi_mulai_pukul']);
        $stmt->bindParam(":operasi_mulai_pukul", $data['operasi_mulai_pukul']);
        $stmt->bindParam(":ekstubasi_pukul", $data['ekstubasi_pukul']);
        $stmt->bindParam(":pasien_keluar_ok", $data['pasien_keluar_ok']);
    
        return $stmt->execute();
    }

    public function createVitalSignAnestesi($data) {
        $query = "INSERT INTO tbl_anestesi_vital_sign 
                 SET no_rawat=:no_rawat, kode_paket=:kode_paket, tanggal=:tanggal, 
                     jam_mulai=:jam_mulai, waktu=:waktu, respirasi=:respirasi, 
                     nadi=:nadi, td_sistolik=:td_sistolik, td_diastolik=:td_diastolik, spo2=:spo2";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":no_rawat", $data['no_rawat']);
        $stmt->bindParam(":kode_paket", $data['kode_paket']);
        $stmt->bindParam(":tanggal", $data['tanggal']);
        $stmt->bindParam(":jam_mulai", $data['jam_mulai']);
        $stmt->bindParam(":waktu", $data['waktu']);
        $stmt->bindParam(":respirasi", $data['respirasi']);
        $stmt->bindParam(":nadi", $data['nadi']);
        $stmt->bindParam(":td_sistolik", $data['td_sistolik']);
        $stmt->bindParam(":td_diastolik", $data['td_diastolik']);
        $stmt->bindParam(":spo2", $data['spo2']);
        
        return $stmt->execute();
    }

    public function createInformedConsent($data) {
        $query = "INSERT INTO tbl_anestesi_informed_consent_anestesi 
                SET id=:id, no_rawat=:no_rawat, kode_paket=:kode_paket, 
                    tanggal=:tanggal, jam_mulai=:jam_mulai, ruang=:ruang,
                    dokter_pelaksana=:dokter_pelaksana, pemberi_info=:pemberi_info,
                    jabatan=:jabatan, penerima_info=:penerima_info,
                    hubungan_pasien=:hubungan_pasien, tindakan_operasi=:tindakan_operasi,
                    jenis_anestesi=:jenis_anestesi, indikasi=:indikasi,
                    tata_cara=:tata_cara, tujuan=:tujuan, risiko=:risiko";
        
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bindParam(":id", $data['id']);
        $stmt->bindParam(":no_rawat", $data['no_rawat']);
        $stmt->bindParam(":kode_paket", $data['kode_paket']);
        $stmt->bindParam(":tanggal", $data['tanggal']);
        $stmt->bindParam(":jam_mulai", $data['jam_mulai']);
        $stmt->bindParam(":ruang", $data['ruang']);
        $stmt->bindParam(":dokter_pelaksana", $data['dokter_pelaksana']);
        $stmt->bindParam(":pemberi_info", $data['pemberi_info']);
        $stmt->bindParam(":jabatan", $data['jabatan']);
        $stmt->bindParam(":penerima_info", $data['penerima_info']);
        $stmt->bindParam(":hubungan_pasien", $data['hubungan_pasien']);
        $stmt->bindParam(":tindakan_operasi", $data['tindakan_operasi']);
        $stmt->bindParam(":jenis_anestesi", $data['jenis_anestesi']);
        $stmt->bindParam(":indikasi", $data['indikasi']);
        $stmt->bindParam(":tata_cara", $data['tata_cara']);
        $stmt->bindParam(":tujuan", $data['tujuan']);
        $stmt->bindParam(":risiko", $data['risiko']);
        
        return $stmt->execute();
    }

    public function createKonsultasiAnestesi($data) {
        $query = "INSERT INTO tbl_anestesi_konsultasi_anestesi 
                SET id=:id, no_rawat=:no_rawat, kode_paket=:kode_paket, 
                    tanggal=:tanggal, jam_mulai=:jam_mulai, tinggi_badan=:tinggi_badan,
                    berat_badan=:berat_badan, diagnosa_pra_operasi=:diagnosa_pra_operasi,
                    rencana_operasi=:rencana_operasi, kondisi_khusus=:kondisi_khusus,
                    tanggal_dibuat=:tanggal_dibuat, jawaban_konsul=:jawaban_konsul";
        
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters
        $stmt->bindParam(":id", $data['id']);
        $stmt->bindParam(":no_rawat", $data['no_rawat']);
        $stmt->bindParam(":kode_paket", $data['kode_paket']);
        $stmt->bindParam(":tanggal", $data['tanggal']);
        $stmt->bindParam(":jam_mulai", $data['jam_mulai']);
        $stmt->bindParam(":tinggi_badan", $data['tinggi_badan']);
        $stmt->bindParam(":berat_badan", $data['berat_badan']);
        $stmt->bindParam(":diagnosa_pra_operasi", $data['diagnosa_pra_operasi']);
        $stmt->bindParam(":rencana_operasi", $data['rencana_operasi']);
        $stmt->bindParam(":kondisi_khusus", $data['kondisi_khusus']);
        $stmt->bindParam(":tanggal_dibuat", $data['tanggal_dibuat']);
        $stmt->bindParam(":jawaban_konsul", $data['jawaban_konsul']);
        
        return $stmt->execute();
    }
}
?>