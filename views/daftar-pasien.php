<?php
$page_title = "Daftar Pasien Booking Operasi";
require_once __DIR__ . '/../config/database.php';

// Koneksi database
$database = new Database();
$db = $database->getConnection();

// Filter & Sorting parameters
$filter_status = isset($_GET['filter_status']) ? $_GET['filter_status'] : '';
$filter_tanggal = isset($_GET['filter_tanggal']) ? $_GET['filter_tanggal'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'tanggal';
$sort_order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'DESC';

// Pagination setup
$limit = 10; // Jumlah data per halaman
$current_page = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
$current_page = max(1, $current_page); // Minimal halaman 1
$offset = ($current_page - 1) * $limit;

// Build WHERE clause untuk filter
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

// Validasi sort_by untuk keamanan
$allowed_sort = ['tanggal', 'jam_mulai', 'status', 'nama_pasien', 'no_rawat'];
if (!in_array($sort_by, $allowed_sort)) {
    $sort_by = 'tanggal';
}

// Validasi sort_order
$sort_order = strtoupper($sort_order) === 'ASC' ? 'ASC' : 'DESC';

// Map sort_by ke kolom database
$sort_column_map = [
    'tanggal' => 'b.tanggal',
    'jam_mulai' => 'b.jam_mulai',
    'status' => 'b.status',
    'nama_pasien' => 'p.nama',
    'no_rawat' => 'b.no_rawat'
];
$sort_column = $sort_column_map[$sort_by];

// Query untuk menghitung total data dengan filter
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

// Query untuk mengambil data booking operasi dengan JOIN, filter, dan sorting
// COLLATE ditambahkan untuk mengatasi perbedaan collation antara tabel
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

// Variabel kosong untuk header (tidak perlu stiker pasien di daftar)
$no_rawat = '';
$kode_paket = '';
$tanggal = '';
$pasien = [];

include __DIR__ . '/../includes/assets.php';
?>

<div class="container">
    <div class="header-actions">
        <h2>Daftar Pasien Booking Operasi</h2>
        <a href="index.php?page=tambah-booking" class="btn btn-success">+ Tambah Booking Baru</a>
    </div>

    <?php
    $show_notif = false;
    if (isset($_GET['status'])) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $show_notif = true;
        }
    }
    ?>

    <!-- Filter & Search Section -->
    <div class="filter-section" style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <form method="GET" action="index.php" id="filterForm">
            <input type="hidden" name="page" value="daftar-pasien">
            
            <div style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr auto; gap: 12px; align-items: end;">
                <!-- Search -->
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 13px; color: #333;">
                        <i class="fas fa-search"></i> Cari Pasien
                    </label>
                    <input type="text" 
                           name="search" 
                           id="searchInput"
                           value="<?= htmlspecialchars($search); ?>" 
                           placeholder="Nama, No. Rawat, atau RM..."
                           style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                </div>
                
                <!-- Filter Status -->
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 13px; color: #333;">
                        <i class="fas fa-filter"></i> Status
                    </label>
                    <select name="filter_status" 
                            id="filterStatus"
                            style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                        <option value="">Semua Status</option>
                        <option value="Menunggu" <?= $filter_status === 'Menunggu' ? 'selected' : ''; ?>>Menunggu</option>
                        <option value="Proses Operasi" <?= $filter_status === 'Proses Operasi' ? 'selected' : ''; ?>>Proses Operasi</option>
                        <option value="Selesai" <?= $filter_status === 'Selesai' ? 'selected' : ''; ?>>Selesai</option>
                    </select>
                </div>
                
                <!-- Filter Tanggal -->
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 13px; color: #333;">
                        <i class="fas fa-calendar"></i> Tanggal
                    </label>
                    <input type="date" 
                           name="filter_tanggal" 
                           id="filterTanggal"
                           value="<?= htmlspecialchars($filter_tanggal); ?>"
                           style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                </div>
                
                <!-- Sort By -->
                <div>
                    <label style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 13px; color: #333;">
                        <i class="fas fa-sort"></i> Urutkan
                    </label>
                    <select name="sort_by" 
                            id="sortBy"
                            style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                        <option value="tanggal" <?= $sort_by === 'tanggal' ? 'selected' : ''; ?>>Tanggal</option>
                        <option value="jam_mulai" <?= $sort_by === 'jam_mulai' ? 'selected' : ''; ?>>Jam</option>
                        <option value="nama_pasien" <?= $sort_by === 'nama_pasien' ? 'selected' : ''; ?>>Nama Pasien</option>
                        <option value="no_rawat" <?= $sort_by === 'no_rawat' ? 'selected' : ''; ?>>No. Rawat</option>
                        <option value="status" <?= $sort_by === 'status' ? 'selected' : ''; ?>>Status</option>
                    </select>
                </div>
                
                <!-- Buttons -->
                <div style="display: flex; gap: 8px;">
                    <button type="submit" 
                            class="btn btn-primary" 
                            style="padding: 10px 20px; white-space: nowrap;">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="index.php?page=daftar-pasien" 
                       class="btn" 
                       style="padding: 10px 16px; background: #6c757d; color: white; text-decoration: none; border-radius: 6px; display: inline-flex; align-items: center;">
                        <i class="fas fa-redo"></i>
                    </a>
                </div>
            </div>
            
            <!-- Sort Order (Hidden, toggled by button) -->
            <input type="hidden" name="sort_order" value="<?= $sort_order; ?>" id="sortOrderInput">
        </form>
        
        <!-- Active Filters Display -->
        <?php if (!empty($filter_status) || !empty($filter_tanggal) || !empty($search)): ?>
        <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #eee;">
            <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                <span style="font-size: 13px; color: #666; font-weight: 600;">Filter Aktif:</span>
                <?php if (!empty($search)): ?>
                <span style="background: #e3f2fd; color: #1976d2; padding: 4px 12px; border-radius: 12px; font-size: 12px;">
                    <i class="fas fa-search"></i> "<?= htmlspecialchars($search); ?>"
                </span>
                <?php endif; ?>
                <?php if (!empty($filter_status)): ?>
                <span style="background: #fff3cd; color: #856404; padding: 4px 12px; border-radius: 12px; font-size: 12px;">
                    <i class="fas fa-tag"></i> <?= htmlspecialchars($filter_status); ?>
                </span>
                <?php endif; ?>
                <?php if (!empty($filter_tanggal)): ?>
                <span style="background: #d4edda; color: #155724; padding: 4px 12px; border-radius: 12px; font-size: 12px;">
                    <i class="fas fa-calendar"></i> <?= date('d/m/Y', strtotime($filter_tanggal)); ?>
                </span>
                <?php endif; ?>
                <span style="font-size: 13px; color: #666;">| Ditemukan: <strong><?= $total_data; ?></strong> data</span>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>No. Rawat</th>
                <th>Nama Pasien</th>
                <th>Kode Paket</th>
                <th>Tanggal Operasi</th>
                <th>Jam Mulai</th>
                <th>Dokter Rawat</th>
                <th>Ruang Rawat</th>
                <th>Perawat</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody id="tableBody">
            <?php if (count($pasien_list) > 0): ?>
                <?php foreach ($pasien_list as $pasien): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($pasien['no_rawat']); ?></td>
                        <td><strong><?php echo htmlspecialchars($pasien['nama_pasien'] ?? '-'); ?></strong></td>
                        <td>
                            <span class="kode-paket"><?php echo htmlspecialchars($pasien['kode_paket']); ?></span>
                        </td>
                        <td><?php echo htmlspecialchars($pasien['tanggal']); ?></td>
                        <td><?php echo htmlspecialchars($pasien['jam_mulai']); ?></td>
                        <td>
                            <?php 
                            if (!empty($pasien['nama_dokter'])) {
                                echo '<strong>' . htmlspecialchars($pasien['nama_dokter']) . '</strong>';
                            } else {
                                echo '<span style="color: #999;">Belum ditentukan</span>';
                            }
                            ?>
                        </td>
                        <td>
                            <?php 
                            if (!empty($pasien['nama_ruang'])) {
                                echo '<strong>' . htmlspecialchars($pasien['nama_ruang']) . '</strong>';
                            } else {
                                echo '<span style="color: #999;">Belum ditentukan</span>';
                            }
                            ?>
                        </td>
                        <td>
                            <?php 
                            if (!empty($pasien['nama_perawat'])) {
                                echo htmlspecialchars($pasien['nama_perawat']);
                            } else {
                                echo '<span style="color: #999;">Belum ditentukan</span>';
                            }
                            ?>
                        </td>
                        <td>
                            <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $pasien['status'])); ?>">
                                <?php echo htmlspecialchars($pasien['status']); ?>
                            </span>
                        </td>
                        <td>
                            <a href="index.php?page=detail-pasien&no_rawat=<?php echo urlencode($pasien['no_rawat']); ?>&kode_paket=<?php echo urlencode($pasien['kode_paket']); ?>&tanggal=<?php echo urlencode($pasien['tanggal']); ?>&jam_mulai=<?php echo urlencode($pasien['jam_mulai']); ?>" 
                               class="btn btn-info btn-sm">Detail</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="10" class="text-center">
                        <p>Belum ada data booking operasi.</p>
                        <a href="index.php?page=tambah-booking" class="btn btn-primary">Tambah Booking Pertama</a>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <?php 
    // Build query string untuk pagination (preserve filter & sort)
    $pagination_params = [
        'page' => 'daftar-pasien',
        'search' => $search,
        'filter_status' => $filter_status,
        'filter_tanggal' => $filter_tanggal,
        'sort_by' => $sort_by,
        'sort_order' => $sort_order
    ];
    // Remove empty values
    $pagination_params = array_filter($pagination_params, function($value) {
        return $value !== '' && $value !== null;
    });
    
    function buildPaginationUrl($params, $page_num) {
        $params['halaman'] = $page_num;
        return '?' . http_build_query($params);
    }
    ?>
    
    <?php if ($total_pages > 1): ?>
    <div id="paginationContainer" class="pagination-container" style="margin-top: 20px; display: flex; justify-content: space-between; align-items: center;">
        <div class="pagination-info">
            <p style="margin: 0; color: #666;">
                Menampilkan <?php echo min($offset + 1, $total_data); ?> - <?php echo min($offset + $limit, $total_data); ?> 
                dari <?php echo $total_data; ?> data
            </p>
        </div>
        
        <div id="paginationButtons" class="pagination" style="display: flex; gap: 5px;">
            <!-- Tombol Previous -->
            <?php if ($current_page > 1): ?>
                <a href="<?php echo buildPaginationUrl($pagination_params, $current_page - 1); ?>" 
                   class="btn btn-sm" 
                   style="padding: 8px 12px; background: #007bff; color: white; text-decoration: none; border-radius: 4px;">
                    &laquo; Prev
                </a>
            <?php else: ?>
                <span class="btn btn-sm" 
                      style="padding: 8px 12px; background: #ccc; color: #666; border-radius: 4px; cursor: not-allowed;">
                    &laquo; Prev
                </span>
            <?php endif; ?>

            <!-- Nomor Halaman -->
            <?php
            // Tampilkan maksimal 5 nomor halaman
            $start_page = max(1, $current_page - 2);
            $end_page = min($total_pages, $current_page + 2);
            
            // Jika di awal, tampilkan lebih banyak halaman berikutnya
            if ($current_page <= 3) {
                $end_page = min($total_pages, 5);
            }
            
            // Jika di akhir, tampilkan lebih banyak halaman sebelumnya
            if ($current_page > $total_pages - 3) {
                $start_page = max(1, $total_pages - 4);
            }
            
            // Tombol halaman pertama jika tidak terlihat
            if ($start_page > 1): ?>
                <a href="<?php echo buildPaginationUrl($pagination_params, 1); ?>" 
                   class="btn btn-sm" 
                   style="padding: 8px 12px; background: white; color: #007bff; border: 1px solid #007bff; text-decoration: none; border-radius: 4px;">
                    1
                </a>
                <?php if ($start_page > 2): ?>
                    <span style="padding: 8px 12px;">...</span>
                <?php endif; ?>
            <?php endif; ?>

            <!-- Loop nomor halaman -->
            <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                <?php if ($i == $current_page): ?>
                    <span class="btn btn-sm" 
                          style="padding: 8px 12px; background: #007bff; color: white; border-radius: 4px; font-weight: bold;">
                        <?php echo $i; ?>
                    </span>
                <?php else: ?>
                    <a href="<?php echo buildPaginationUrl($pagination_params, $i); ?>" 
                       class="btn btn-sm" 
                       style="padding: 8px 12px; background: white; color: #007bff; border: 1px solid #007bff; text-decoration: none; border-radius: 4px;">
                        <?php echo $i; ?>
                    </a>
                <?php endif; ?>
            <?php endfor; ?>

            <!-- Tombol halaman terakhir jika tidak terlihat -->
            <?php if ($end_page < $total_pages): ?>
                <?php if ($end_page < $total_pages - 1): ?>
                    <span style="padding: 8px 12px;">...</span>
                <?php endif; ?>
                <a href="<?php echo buildPaginationUrl($pagination_params, $total_pages); ?>" 
                   class="btn btn-sm" 
                   style="padding: 8px 12px; background: white; color: #007bff; border: 1px solid #007bff; text-decoration: none; border-radius: 4px;">
                    <?php echo $total_pages; ?>
                </a>
            <?php endif; ?>

            <!-- Tombol Next -->
            <?php if ($current_page < $total_pages): ?>
                <a href="<?php echo buildPaginationUrl($pagination_params, $current_page + 1); ?>" 
                   class="btn btn-sm" 
                   style="padding: 8px 12px; background: #007bff; color: white; text-decoration: none; border-radius: 4px;">
                    Next &raquo;
                </a>
            <?php else: ?>
                <span class="btn btn-sm" 
                      style="padding: 8px 12px; background: #ccc; color: #666; border-radius: 4px; cursor: not-allowed;">
                    Next &raquo;
                </span>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    
    <!-- AJAX Live Search Script -->
<script>
// Disable form exit confirmation
window.onbeforeunload = null;
if (typeof window.formDirty !== 'undefined') {
    window.formDirty = false;
}

// Current page state
let currentPage = <?php echo $current_page; ?>;

// Debounce function
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func(...args), wait);
    };
}

// Show loading indicator
function showLoading() {
    const tableBody = document.getElementById('tableBody');
    tableBody.innerHTML = `
        <tr>
            <td colspan="10" style="text-align: center; padding: 40px;">
                <div style="display: inline-block; border: 4px solid #f3f3f3; border-top: 4px solid #007bff; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite;"></div>
                <p style="margin-top: 10px; color: #666;">Memuat data...</p>
            </td>
        </tr>
    `;
    
    // Add animation
    if (!document.getElementById('spinAnimation')) {
        const style = document.createElement('style');
        style.id = 'spinAnimation';
        style.textContent = `@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }`;
        document.head.appendChild(style);
    }
}

// Fetch data via AJAX
function fetchData(page = 1) {
    showLoading();
    
    const search = document.getElementById('searchInput').value;
    const filterStatus = document.getElementById('filterStatus').value;
    const filterTanggal = document.getElementById('filterTanggal').value;
    const sortBy = document.getElementById('sortBy').value;
    
    const params = new URLSearchParams({
        search: search,
        filter_status: filterStatus,
        filter_tanggal: filterTanggal,
        sort_by: sortBy,
        sort_order: 'DESC',
        halaman: page
    });
    
    fetch('api/search-pasien.php?' + params.toString())
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderTable(data.data);
                renderPagination(data.pagination);
                currentPage = page;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('tableBody').innerHTML = `
                <tr>
                    <td colspan="10" style="text-align: center; padding: 40px; color: red;">
                        <i class="fas fa-exclamation-triangle"></i> Terjadi kesalahan saat memuat data
                    </td>
                </tr>
            `;
        });
}

// Render table
function renderTable(data) {
    const tableBody = document.getElementById('tableBody');
    
    if (data.length === 0) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="10" class="text-center" style="padding: 40px;">
                    <p>Tidak ada data ditemukan.</p>
                    <a href="index.php?page=tambah-booking" class="btn btn-primary">Tambah Booking Baru</a>
                </td>
            </tr>
        `;
        return;
    }
    
    let html = '';
    data.forEach(pasien => {
        const statusClass = pasien.status.toLowerCase().replace(/ /g, '-');
        html += `
            <tr>
                <td>${escapeHtml(pasien.no_rawat)}</td>
                <td><strong>${escapeHtml(pasien.nama_pasien || '-')}</strong></td>
                <td><span class="kode-paket">${escapeHtml(pasien.kode_paket)}</span></td>
                <td>${escapeHtml(pasien.tanggal)}</td>
                <td>${escapeHtml(pasien.jam_mulai)}</td>
                <td>${pasien.nama_dokter ? '<strong>' + escapeHtml(pasien.nama_dokter) + '</strong>' : '<span style="color: #999;">Belum ditentukan</span>'}</td>
                <td>${pasien.nama_ruang ? '<strong>' + escapeHtml(pasien.nama_ruang) + '</strong>' : '<span style="color: #999;">Belum ditentukan</span>'}</td>
                <td>${pasien.nama_perawat ? escapeHtml(pasien.nama_perawat) : '<span style="color: #999;">Belum ditentukan</span>'}</td>
                <td><span class="status-badge status-${statusClass}">${escapeHtml(pasien.status)}</span></td>
                <td>
                    <a href="index.php?page=detail-pasien&no_rawat=${encodeURIComponent(pasien.no_rawat)}&kode_paket=${encodeURIComponent(pasien.kode_paket)}&tanggal=${encodeURIComponent(pasien.tanggal)}&jam_mulai=${encodeURIComponent(pasien.jam_mulai)}" 
                       class="btn btn-info btn-sm">Detail</a>
                </td>
            </tr>
        `;
    });
    tableBody.innerHTML = html;
}

// Render pagination
function renderPagination(pagination) {
    const container = document.getElementById('paginationContainer');
    if (!container) return;
    
    if (pagination.total_pages <= 1) {
        container.style.display = 'none';
        return;
    }
    
    container.style.display = 'flex';
    
    const showing = `Menampilkan ${Math.min(pagination.offset + 1, pagination.total_data)} - ${Math.min(pagination.offset + pagination.per_page, pagination.total_data)} dari ${pagination.total_data} data`;
    container.querySelector('.pagination-info p').textContent = showing;
    
    let html = '';
    
    // Previous button
    if (pagination.current_page > 1) {
        html += `<a href="#" onclick="fetchData(${pagination.current_page - 1}); return false;" class="btn btn-sm" style="padding: 8px 12px; background: #007bff; color: white; text-decoration: none; border-radius: 4px;">&laquo; Prev</a>`;
    } else {
        html += `<span class="btn btn-sm" style="padding: 8px 12px; background: #ccc; color: #666; border-radius: 4px; cursor: not-allowed;">&laquo; Prev</span>`;
    }
    
    // Page numbers
    const startPage = Math.max(1, pagination.current_page - 2);
    const endPage = Math.min(pagination.total_pages, pagination.current_page + 2);
    
    if (startPage > 1) {
        html += `<a href="#" onclick="fetchData(1); return false;" class="btn btn-sm" style="padding: 8px 12px; background: white; color: #007bff; border: 1px solid #007bff; text-decoration: none; border-radius: 4px;">1</a>`;
        if (startPage > 2) html += `<span style="padding: 8px 12px;">...</span>`;
    }
    
    for (let i = startPage; i <= endPage; i++) {
        if (i === pagination.current_page) {
            html += `<span class="btn btn-sm" style="padding: 8px 12px; background: #007bff; color: white; border-radius: 4px; font-weight: bold;">${i}</span>`;
        } else {
            html += `<a href="#" onclick="fetchData(${i}); return false;" class="btn btn-sm" style="padding: 8px 12px; background: white; color: #007bff; border: 1px solid #007bff; text-decoration: none; border-radius: 4px;">${i}</a>`;
        }
    }
    
    if (endPage < pagination.total_pages) {
        if (endPage < pagination.total_pages - 1) html += `<span style="padding: 8px 12px;">...</span>`;
        html += `<a href="#" onclick="fetchData(${pagination.total_pages}); return false;" class="btn btn-sm" style="padding: 8px 12px; background: white; color: #007bff; border: 1px solid #007bff; text-decoration: none; border-radius: 4px;">${pagination.total_pages}</a>`;
    }
    
    // Next button
    if (pagination.current_page < pagination.total_pages) {
        html += `<a href="#" onclick="fetchData(${pagination.current_page + 1}); return false;" class="btn btn-sm" style="padding: 8px 12px; background: #007bff; color: white; text-decoration: none; border-radius: 4px;">Next &raquo;</a>`;
    } else {
        html += `<span class="btn btn-sm" style="padding: 8px 12px; background: #ccc; color: #666; border-radius: 4px; cursor: not-allowed;">Next &raquo;</span>`;
    }
    
    document.getElementById('paginationButtons').innerHTML = html;
}

// Escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Live search with debounce
const searchInput = document.getElementById('searchInput');
if (searchInput) {
    const handleSearch = debounce(function() {
        fetchData(1); // Reset to page 1 on new search
    }, 500);
    
    searchInput.addEventListener('input', handleSearch);
    
    // Visual feedback
    searchInput.addEventListener('input', function() {
        if (this.value.length > 0) {
            this.style.borderColor = '#007bff';
            this.style.boxShadow = '0 0 0 0.2rem rgba(0,123,255,.25)';
        } else {
            this.style.borderColor = '#ddd';
            this.style.boxShadow = 'none';
        }
    });
}

// Auto-submit on filter change
document.getElementById('filterStatus').addEventListener('change', () => fetchData(1));
document.getElementById('filterTanggal').addEventListener('change', () => fetchData(1));
document.getElementById('sortBy').addEventListener('change', () => fetchData(1));

// Prevent form submit
document.getElementById('filterForm').addEventListener('submit', function(e) {
    e.preventDefault();
    fetchData(1);
});
</script>
