<?php
// controllers/CustomerController.php

// 1. Load Database (Hanya Sekali & Tangkap Variabelnya)
$pdo = require_once '../config/database.php';

// 2. Load Model
require_once '../models/Pelanggan.php';

// 3. Cek Koneksi (Safety Check)
if (!$pdo || !is_object($pdo)) {
    die("❌ Gagal memuat koneksi database di Controller. Cek file config/database.php pastikan ada 'return \$pdo;' di akhir.");
}

$pelangganModel = new Pelanggan($pdo);

// 4. Tangkap Action
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {

    // === CREATE ===
    case 'create':
        $nama = trim($_POST['nama']);
        $no_hp = trim($_POST['no_hp']);
        $alamat = trim($_POST['alamat']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        // Cek konfirmasi password
        if ($password !== $confirm_password) {
            header("Location: ../views/admin/customers.php?view=create&msg=Password+tidak+cocok");
            exit;
        }

        // Eksekusi Simpan
        // Jika gagal, Model akan menampilkan errornya di layar karena kita pasang die()
        if ($pelangganModel->create($nama, $no_hp, $alamat, $email, $password)) {
            header("Location: ../views/admin/customers.php?msg=Berhasil+tambah+customer");
        } else {
            // Baris ini jarang tereksekusi jika die() di model menyala
            header("Location: ../views/admin/customers.php?view=create&msg=Gagal+simpan+data");
        }
        break;

    // === UPDATE ===
    case 'update':
        $id = (int) $_POST['id_pelanggan'];
        $nama = trim($_POST['nama']);
        $no_hp = trim($_POST['no_hp']);
        $alamat = trim($_POST['alamat']);
        $email = trim($_POST['email']);

        if ($pelangganModel->update($id, $nama, $no_hp, $alamat, $email)) {
            header("Location: ../views/admin/customers.php?msg=Berhasil+update+customer");
        } else {
            header("Location: ../views/admin/customers.php?view=edit&id=$id&msg=Gagal+update");
        }
        break;

    // === DELETE ===
    case 'delete':
        $id = (int) $_GET['id'];

        if ($pelangganModel->delete($id)) {
            header("Location: ../views/admin/customers.php?msg=Data+terhapus");
        } else {
            header("Location: ../views/admin/customers.php?msg=Gagal+hapus");
        }
        break;

    default:
        header("Location: ../views/admin/customers.php");
        break;
}
?>