<?php
// controllers/SparepartController.php

// 🔥 NYALAKAN ERROR REPORTING AGAR TIDAK BLANK PUTIH
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../config/database.php';
require_once '../models/Sparepart.php';

// Cek koneksi
if (!$pdo || !is_object($pdo)) {
    die("❌ Gagal memuat koneksi database. Cek file config/database.php.");
}

$sparepartModel = new Sparepart($pdo);
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Debugging: Jika tidak ada action, tampilkan pesan
if (empty($action)) {
    die("❌ Error: Tidak ada Action yang diterima. Pastikan form HTML memiliki name='action'.");
}

switch ($action) {

    // === CREATE ===
    case 'create':
        $nama = trim($_POST['nama_sparepart']);
        $stok = (int) $_POST['stok'];
        $harga = (float) $_POST['harga'];
        $merek = trim($_POST['merek']);

        if ($sparepartModel->create($nama, $stok, $harga, $merek)) {
            // BERHASIL: Balik ke sparepart.php
            header("Location: ../views/admin/sparepart.php?msg=Berhasil+tambah");
        } else {
            // GAGAL
            header("Location: ../views/admin/sparepart.php?view=create&msg=Gagal+simpan");
        }
        break;

    // === UPDATE ===
    case 'update':
        $id = (int) $_POST['id_sparepart'];
        $nama = trim($_POST['nama_sparepart']);
        $stok = (int) $_POST['stok'];
        $harga = (float) $_POST['harga'];
        $merek = trim($_POST['merek']);

        if ($sparepartModel->update($id, $nama, $stok, $harga, $merek)) {
            header("Location: ../views/admin/sparepart.php?msg=Berhasil+update");
        } else {
            header("Location: ../views/admin/sparepart.php?view=edit&id=$id&msg=Gagal+update");
        }
        break;

    // === DELETE ===
    case 'delete':
        $id = (int) $_GET['id'];
        if ($sparepartModel->delete($id)) {
            header("Location: ../views/admin/sparepart.php?msg=Data+terhapus");
        } else {
            header("Location: ../views/admin/sparepart.php?msg=Gagal+hapus");
        }
        break;

    default:
        header("Location: ../views/admin/sparepart.php");
        break;
}
?>