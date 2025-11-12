<?php
// API endpoint untuk AJAX search
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

// Koneksi database
$database = new Database();
$db = $database->getConnection();

// Get parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_status = isset($_GET['filter_status']) ? $_GET['filter_status'] : '';
$filter_tanggal = isset($_GET['filter_tanggal']) ? $_GET['filter_tanggal'] : '';
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'tanggal';
$sort_order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'DESC';
$halaman = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;

// Pagination
$limit = 10;
$offset = ($halaman - 1) * $limit;

// Build WHERE clause
$where_conditions = [];
$params = [];

if (!empty($filter_status)) {
    $where_conditions[] = "b.status = :status";
    $params[':status'] = $filter_status;
}

if (!empty($filter_tanggal)) {
    $where_conditions[] = "b.tanggal = :tanggal";
    $params[':tanggal'] = $filter_tanggal;
}

if (!empty($search)) {
    $where_conditions[] = "(p.nama LIKE :search OR b.no_rawat LIKE :search OR p.kode_rekam_medis LIKE :search)";
    $params[':search'] = "%$search%";
}

$where_clause = '';
if (count($where_conditions) > 0) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

// Validate sort
$allowed_sort = ['tanggal', 'jam_mulai', 'status', 'nama_pasien', 'no_rawat'];
if (!in_array($sort_by, $allowed_sort)) {
    $sort_by = 'tanggal';
}
$sort_order = strtoupper($sort_order) === 'ASC' ? 'ASC' : 'DESC';

$sort_column_map = [
    'tanggal' => 'b.tanggal',
    'jam_mulai' => 'b.jam_mulai',
    'status' => 'b.status',
    'nama_pasien' => 'p.nama',
    'no_rawat' => 'b.no_rawat'
];
$sort_column = $sort_column_map[$sort_by];

// Count total
$count_query = "SELECT COUNT(*) as total 
                FROM booking_operasi b
                LEFT JOIN pasien p ON b.kd_pasien = p.kd_pasien
                $where_clause";
$count_stmt = $db->prepare($count_query);
foreach ($params as $key => $value) {
    $count_stmt->bindValue($key, $value);
}
$count_stmt->execute();
$total_data = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_data / $limit);

// Get data
$query = "SELECT 
            b.no_rawat, 
            b.kode_paket, 
            b.tanggal, 
            b.jam_mulai, 
            b.jam_selesai,
            b.status, 
            b.kd_dokter, 
            b.kd_ruang_ok,
            b.kd_pasien,
            b.dokter_rawat,
            b.ruang_rawat,
            b.perawat,
            p.nama as nama_pasien,
            p.kode_rekam_medis,
            p.alamat,
            p.tanggal_lahir,
            p.jenis_kelamin,
            d.nama_dokter,
            r.nama_ruang,
            pr.nama_perawat
          FROM booking_operasi b
          LEFT JOIN pasien p ON b.kd_pasien = p.kd_pasien
          LEFT JOIN tbl_dokter d ON b.dokter_rawat COLLATE utf8mb4_unicode_ci = d.id_dokter
          LEFT JOIN tbl_ruang r ON b.ruang_rawat COLLATE utf8mb4_unicode_ci = r.id_ruang
          LEFT JOIN tbl_perawat pr ON b.perawat COLLATE utf8mb4_unicode_ci = pr.id_perawat
          $where_clause
          ORDER BY $sort_column $sort_order
          LIMIT :limit OFFSET :offset";

$stmt = $db->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$pasien_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Return JSON
echo json_encode([
    'success' => true,
    'data' => $pasien_list,
    'pagination' => [
        'current_page' => $halaman,
        'total_pages' => $total_pages,
        'total_data' => $total_data,
        'per_page' => $limit,
        'offset' => $offset
    ]
]);
