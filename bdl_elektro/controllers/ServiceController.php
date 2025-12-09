<?php
// controllers/ServiceController.php

// Debug Error
ini_set('display_errors', 1);
error_reporting(E_ALL);

$pdo = require_once '../config/database.php';
require_once '../models/Service.php';

if (!$pdo || !is_object($pdo)) {
    die("❌ Koneksi Database Gagal di Controller.");
}

$serviceModel = new Service($pdo);
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {

    case 'create':
        $id_perangkat = (int) $_POST['id_perangkat'];
        // Handle teknisi opsional
        $id_teknisi = !empty($_POST['id_teknisi']) ? (int) $_POST['id_teknisi'] : null;
        $keluhan = trim($_POST['keluhan']);
        $biaya_estimasi = (float) $_POST['biaya_estimasi'];

        // Cek ID Perangkat
        if (empty($id_perangkat)) {
            die("❌ ID Perangkat tidak boleh kosong. Pilih perangkat dulu.");
        }

        if ($serviceModel->create($id_perangkat, $id_teknisi, $keluhan, $biaya_estimasi)) {
            header("Location: ../views/admin/services.php?msg=Sukses+tambah+service");
        } else {
            // Ini jarang tereksekusi karena ada die() di model
            header("Location: ../views/admin/services.php?view=create&msg=Gagal+tambah");
        }
        break;

    case 'update_status':
        $id_service = (int) $_POST['id_service'];
        $id_status = (int) $_POST['id_status'];
        $catatan = trim($_POST['catatan_internal'] ?? '');

        if ($serviceModel->updateStatus($id_service, $id_status, $catatan)) {
            header("Location: ../views/admin/services.php?view=track&id=$id_service&msg=Status+diupdate");
        } else {
            header("Location: ../views/admin/services.php?view=track&id=$id_service&msg=Gagal+update");
        }
        break;

    case 'complete':
        $id_service = (int) $_POST['id_service'];
        $biaya_akhir = (float) $_POST['biaya_akhir'];

        if ($serviceModel->completeService($id_service, $biaya_akhir)) {
            header("Location: ../views/admin/services.php?view=track&id=$id_service&msg=Service+Selesai");
        } else {
            header("Location: ../views/admin/services.php?view=track&id=$id_service&msg=Gagal+selesai");
        }
        break;

    default:
        header("Location: ../views/admin/services.php");
        break;
}
?>