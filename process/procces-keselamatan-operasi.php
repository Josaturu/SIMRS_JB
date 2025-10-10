<?php
require_once '../config/database.php';
require_once '../models/FormModel.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $database = new Database();
        $db = $database->getConnection();
        $form = new FormModel($db);
        
        // Collect all form data
        $formData = $_POST;
        
        // Prepare signin data
        $signin = [
            'time' => $formData['signin_time'] ?? '',
            'items' => [
                'konfirmasi_pasien' => [
                    'identitas' => isset($formData['signin_1a']),
                    'lokasi' => isset($formData['signin_1b']),
                    'prosedur' => isset($formData['signin_1c']),
                    'surat_izin' => isset($formData['signin_1d'])
                ],
                'tanda_lokasi' => [
                    'ya' => isset($formData['signin_2a']),
                    'tidak_dilakukan' => isset($formData['signin_2b'])
                ],
                'cek_anestesi' => isset($formData['signin_3']),
                'pulse_oximeter' => isset($formData['signin_4']),
                'riwayat_alergi' => [
                    'tidak' => isset($formData['signin_5a']),
                    'ya' => isset($formData['signin_5b'])
                ],
                'kesulitan_nafas' => [
                    'tidak' => isset($formData['signin_6a']),
                    'peralatan_tersedia' => isset($formData['signin_6b'])
                ],
                'resiko_darah' => [
                    'tidak' => isset($formData['signin_7a']),
                    'iv_terapi' => isset($formData['signin_7b'])
                ]
            ],
            'tim' => [
                'dokter_anestesi' => $formData['dokter_anestesi_signin'] ?? '',
                'perawat_anestesi' => $formData['perawat_anestesi_signin'] ?? '',
                'perawat_sirkuler' => $formData['perawat_sirkuler_signin'] ?? ''
            ]
        ];
        
        // Prepare timeout data
        $timeout = [
            'time' => $formData['timeout_time'] ?? '',
            'items' => [
                'konfirmasi_tim' => isset($formData['timeout_1']),
                'konfirmasi_verbal' => [
                    'nama_pasien' => isset($formData['timeout_2a']),
                    'prosedur' => isset($formData['timeout_2b']),
                    'lokasi_insisi' => isset($formData['timeout_2c'])
                ],
                'antibiotik_profilaksis' => isset($formData['timeout_3']),
                'kejadian_kritis' => [
                    'dokter_bedah' => $formData['catatan_dokter_bedah'] ?? '',
                    'dokter_anestesi' => $formData['catatan_dokter_anestesi'] ?? '',
                    'perawat' => $formData['catatan_perawat'] ?? ''
                ],
                'pemeriksaan_radiologi' => [
                    'ya' => isset($formData['timeout_5a']),
                    'tidak' => isset($formData['timeout_5b'])
                ]
            ],
            'tim' => [
                'perawat_sirkuler' => $formData['perawat_sirkuler_timeout'] ?? ''
            ]
        ];
        
        // Prepare signout data
        $signout = [
            'time' => $formData['signout_time'] ?? '',
            'items' => [
                'konfirmasi_perawat' => [
                    'nama_prosedur' => isset($formData['signout_1a']),
                    'instrumen_lengkap' => isset($formData['signout_1b']),
                    'spesimen_label' => isset($formData['signout_1c']),
                    'tidak_masalah_alat' => isset($formData['signout_1d'])
                ],
                'pembahasan_tim' => isset($formData['signout_2'])
            ],
            'tim' => [
                'perawat_sirkuler' => $formData['perawat_sirkuler_signout'] ?? '',
                'dokter_anestesi' => $formData['dokter_anestesi_signout'] ?? '',
                'operator' => $formData['operator_signout'] ?? ''
            ],
            'tanggal_keluar' => [
                'tanggal' => $formData['tanggal_keluar'] ?? '',
                'tahun' => $formData['tahun_keluar'] ?? ''
            ]
        ];
        
        // Prepare data for database
        $data = [
            'id' => generateUUID(),
            'no_rawat' => sanitizeInput($formData['no_rawat']),
            'kode_paket' => sanitizeInput($formData['kode_paket']),
            'tanggal' => sanitizeInput($formData['tanggal']),
            'jam_mulai' => sanitizeInput($formData['jam_mulai']),
            'operasi' => sanitizeInput($formData['operasi']),
            'tanggal_tindakan' => sanitizeInput($formData['tglTindakan']),
            'signin' => json_encode($signin, JSON_UNESCAPED_UNICODE),
            'timeout' => json_encode($timeout, JSON_UNESCAPED_UNICODE),
            'signout' => json_encode($signout, JSON_UNESCAPED_UNICODE)
        ];
        
        if ($form->createChecklistKeselamatan($data)) {
            header("Location: ../index.php?page=keselamatan-operasi&status=sukses&id=" . $data['id']);
        } else {
            header("Location: ../index.php?page=keselamatan-operasi&status=gagal");
        }
        
    } catch (Exception $e) {
        error_log("Error submitting keselamatan operasi: " . $e->getMessage());
        header("Location: ../index.php?page=keselamatan-operasi&status=error");
    }
    exit;
} else {
    header("Location: ../index.php?page=keselamatan-operasi");
    exit;
}
?>