<?php
require_once 'config/database.php';

$page = $_GET['page'] ?? 'login';

switch($page) {
    case 'tambah-booking':
        include 'views/tambah-booking.php';
        break;
        
    case 'persiapan-operasi':
        $page_title = "Checklist Persiapan Operasi";
        $document_code = "RMO-1 a";
        include 'views/form-persiapan-operasi.php';
        break;
        
    case 'keselamatan-operasi':
        $page_title = "Checklist Keselamatan Operasi";
        $document_code = "RMOK-0002";
        include 'views/form-keselamatan-operasi.php';
        break;
        
    case 'kamar-pemulihan':
        $page_title = "Catatan Kamar Pemulihan";
        $document_code = "RMOK - 30";
        include 'views/form-kamar-pemulihan.php';
        break;

    case 'form-catatan-sedasi':
        $page_title = "Catatan Sedasi dan Anestesi";
        $document_code = "RMOK-0003";
        include 'views/form-catatan-sedasi.php';
        break;

    case 'informed-consent-anestesi':
        include 'views/form-informed-consent-anestesi.php';
        break;
        
    case 'konsultasi-anestesi':
        include 'views/form-konsultasi-anestesi.php';
        break;
        
    case 'detail-pasien':
        include 'views/detail-pasien.php';
        break;
        
    case 'daftar-pasien':
        include 'views/daftar-pasien.php';
        break;

    case 'dashboard':
        include 'views/dashboard.php';
        break;

    default:
        include 'login.php';
        break;
}
?>
