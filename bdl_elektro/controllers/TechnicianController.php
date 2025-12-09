<?php
// controllers/TechnicianController.php

// 1. Load Database (Sekali & Validasi)
$pdo = require_once '../config/database.php';
require_once '../models/Teknisi.php';

if (!$pdo || !is_object($pdo)) {
    die("Gagal memuat koneksi database.");
}

$teknisiModel = new Teknisi($pdo);
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {

    // === CREATE ===
    case 'create':
        $nama = trim($_POST['nama_teknisi']);
        $keahlian = trim($_POST['keahlian']);
        $no_hp = trim($_POST['no_hp']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        // Checkbox return value '1' or null
        $status_aktif = isset($_POST['status_aktif']);

        if ($password !== $confirm_password) {
            header("Location: ../views/admin/technicians.php?view=create&msg=Password+tidak+cocok");
            exit;
        }

        if ($teknisiModel->create($nama, $keahlian, $no_hp, $email, $password, $status_aktif)) {
            header("Location: ../views/admin/technicians.php?msg=Berhasil+tambah+teknisi");
        } else {
            header("Location: ../views/admin/technicians.php?view=create&msg=Gagal+simpan");
        }
        break;

    // === UPDATE ===
    case 'update':
        $id = (int) $_POST['id_teknisi'];
        $nama = trim($_POST['nama_teknisi']);
        $keahlian = trim($_POST['keahlian']);
        $no_hp = trim($_POST['no_hp']);
        $email = trim($_POST['email']);
        $status_aktif = isset($_POST['status_aktif']);

        if ($teknisiModel->update($id, $nama, $keahlian, $no_hp, $email, $status_aktif)) {
            header("Location: ../views/admin/technicians.php?msg=Berhasil+update+teknisi");
        } else {
            header("Location: ../views/admin/technicians.php?view=edit&id=$id&msg=Gagal+update");
        }
        break;

    // === DELETE ===
    case 'delete':
        $id = (int) $_GET['id'];
        if ($teknisiModel->delete($id)) {
            header("Location: ../views/admin/technicians.php?msg=Data+terhapus");
        } else {
            header("Location: ../views/admin/technicians.php?msg=Gagal+hapus");
        }
        break;

    default:
        header("Location: ../views/admin/technicians.php");
        break;
}
?>