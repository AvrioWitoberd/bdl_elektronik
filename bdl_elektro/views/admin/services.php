<?php
// views/admin/services.php

// Debug Error
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../../config/database.php';
require_once '../../models/Service.php';

// Model Tambahan untuk Dropdown (Manual Query jika file model belum ada)
// Kita pakai PDO langsung saja biar tidak error dependensi file
if (!isset($pdo))
    die("Koneksi DB hilang.");

$serviceModel = new Service($pdo);

$view = $_GET['view'] ?? 'list';
$message = $_GET['msg'] ?? '';

// --- AMBIL DATA DROPDOWN ---

// 1. Status (Wajib ada)
try {
    $stmtSt = $pdo->query("SELECT * FROM status_perbaikan ORDER BY id_status ASC");
    $statusList = $stmtSt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $statusList = []; // Jangan error, kosongkan saja
    $errorStatus = "Tabel status_perbaikan belum di-setup!";
}

// 2. Teknisi (Hanya yang aktif)
try {
    // Cek dulu apakah kolom status_aktif ada, kalau ragu ambil semua
    $stmtTek = $pdo->query("SELECT id_teknisi, nama_teknisi, keahlian FROM teknisi ORDER BY nama_teknisi ASC");
    $teknisiList = $stmtTek->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $teknisiList = [];
}

// 3. Perangkat (Join Pelanggan)
// Digunakan saat View = Create
$perangkatList = [];
if ($view === 'create') {
    try {
        $sqlDev = "SELECT d.id_perangkat, d.model, d.merek, p.nama as pemilik 
                   FROM perangkat d 
                   JOIN pelanggan p ON d.id_pelanggan = p.id_pelanggan 
                   ORDER BY p.nama ASC";
        $perangkatList = $pdo->query($sqlDev)->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $perangkatList = [];
    }
}

// --- LOGIKA UTAMA ---

// A. DETAIL / TRACK
$detail = null;
if ($view === 'track') {
    $id = $_GET['id'] ?? 0;
    $detail = $serviceModel->getById($id);
    if (!$detail)
        die("Data service tidak ditemukan. ID: " . htmlspecialchars($id));
}

// B. LIST DATA
$services = [];
$totalPages = 1;
$page = 1;
$search = '';

if ($view === 'list') {
    $limit = 10;
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    $offset = ($page - 1) * $limit;
    $search = $_GET['search'] ?? '';

    $services = $serviceModel->getAll($limit, $offset, $search);
    $total = $serviceModel->countAll($search);
    $totalPages = ceil($total / $limit);
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Kelola Service</title>
    <style>
        body {
            font-family: sans-serif;
            padding: 20px;
            background-color: #f8f9fa;
        }

        .container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 1000px;
            margin: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #343a40;
            color: white;
        }

        .btn {
            padding: 8px 12px;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            display: inline-block;
            border: none;
            cursor: pointer;
            font-size: 14px;
            margin-right: 5px;
        }

        .btn-blue {
            background-color: #007bff;
        }

        .btn-green {
            background-color: #28a745;
        }

        .btn-orange {
            background-color: #fd7e14;
        }

        .btn-gray {
            background-color: #6c757d;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 8px;
            margin: 5px 0 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .alert {
            background: #d4edda;
            color: #155724;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            border: 1px solid #c3e6cb;
        }

        .card {
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            background: #fff;
        }
    </style>
</head>

<body>

    <?php include '../../views/layouts/header.php'; ?>

    <div class="container" style="margin-top:20px;">

        <?php if (isset($errorStatus)): ?>
            <div style="background:#f8d7da; color:#721c24; padding:10px; margin-bottom:10px;">
                ⚠️ <b>Warning:</b> <?php echo $errorStatus; ?> Silakan jalankan SQL status_perbaikan.
            </div>
        <?php endif; ?>

        <?php if ($message): ?>
            <div class="alert">Info: <?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if ($view === 'create'): ?>
            <h2>Input Service Baru</h2>

            <?php if (empty($perangkatList)): ?>
                <div style="color:red; padding:20px; border:1px solid red;">
                    ⚠️ Belum ada data Perangkat (Device).<br>
                    Silakan tambah Data Pelanggan & Perangkat terlebih dahulu sebelum membuat Service.
                </div>
                <a href="services.php" class="btn btn-gray">Kembali</a>
            <?php else: ?>
                <form method="POST" action="../../controllers/ServiceController.php">
                    <input type="hidden" name="action" value="create">

                    <label>Pilih Perangkat (Milik Pelanggan):</label>
                    <select name="id_perangkat" required>
                        <option value="">-- Pilih --</option>
                        <?php foreach ($perangkatList as $d): ?>
                            <option value="<?php echo $d['id_perangkat']; ?>">
                                <?php echo htmlspecialchars($d['pemilik'] . ' - ' . $d['merek'] . ' ' . $d['model']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label>Keluhan / Kerusakan:</label>
                    <textarea name="keluhan" rows="3" required placeholder="Contoh: Mati total, Layar pecah..."></textarea>

                    <label>Tugaskan Teknisi (Opsional):</label>
                    <select name="id_teknisi">
                        <option value="">-- Belum ada teknisi --</option>
                        <?php foreach ($teknisiList as $t): ?>
                            <option value="<?php echo $t['id_teknisi']; ?>">
                                <?php echo htmlspecialchars($t['nama_teknisi']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label>Estimasi Biaya (Rp):</label>
                    <input type="number" name="biaya_estimasi" value="0">

                    <button type="submit" class="btn btn-green">Simpan Service</button>
                    <a href="services.php" class="btn btn-gray">Batal</a>
                </form>
            <?php endif; ?>

        <?php elseif ($view === 'track'): ?>
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <h2>Detail Service #<?php echo $detail['id_service']; ?></h2>
                <a href="services.php" class="btn btn-gray">Kembali</a>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
                <div class="card">
                    <h3>Info Service</h3>
                    <p><strong>Pelanggan:</strong> <?php echo htmlspecialchars($detail['nama_pelanggan']); ?>
                        (<?php echo htmlspecialchars($detail['hp_pelanggan'] ?? '-'); ?>)</p>
                    <p><strong>Perangkat:</strong> <?php echo htmlspecialchars($detail['nama_perangkat']); ?>
                        (<?php echo htmlspecialchars($detail['merek']); ?>)</p>
                    <p><strong>Keluhan:</strong> <?php echo htmlspecialchars($detail['keluhan']); ?></p>
                    <p><strong>Teknisi:</strong>
                        <?php echo htmlspecialchars($detail['nama_teknisi'] ?? 'Belum assigned'); ?></p>
                    <hr>
                    <p><strong>Status:</strong> <b
                            style="color:#007bff;"><?php echo htmlspecialchars($detail['nama_status']); ?></b></p>
                    <p><strong>Biaya Estimasi:</strong> Rp
                        <?php echo number_format($detail['biaya_estimasi'], 0, ',', '.'); ?></p>
                </div>

                <div>
                    <div class="card">
                        <h3>Update Status</h3>
                        <form method="POST" action="../../controllers/ServiceController.php">
                            <input type="hidden" name="action" value="update_status">
                            <input type="hidden" name="id_service" value="<?php echo $detail['id_service']; ?>">

                            <label>Ganti Status:</label>
                            <select name="id_status">
                                <?php foreach ($statusList as $st): ?>
                                    <option value="<?php echo $st['id_status']; ?>" <?php echo ($st['id_status'] == $detail['id_status']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($st['nama_status']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <label>Catatan (Opsional):</label>
                            <textarea name="catatan_internal" rows="2"></textarea>

                            <button type="submit" class="btn btn-blue">Update Status</button>
                        </form>
                    </div>

                    <?php if (empty($detail['tanggal_selesai'])): ?>
                        <div class="card" style="border:1px solid #28a745;">
                            <h3 style="color:#28a745;">Penyelesaian</h3>
                            <form method="POST" action="../../controllers/ServiceController.php">
                                <input type="hidden" name="action" value="complete">
                                <input type="hidden" name="id_service" value="<?php echo $detail['id_service']; ?>">

                                <label>Biaya Akhir (Rp):</label>
                                <input type="number" name="biaya_akhir" value="<?php echo $detail['biaya_estimasi']; ?>"
                                    required>

                                <button type="submit" class="btn btn-green"
                                    onclick="return confirm('Selesaikan service ini?')">Selesai & Bayar</button>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="alert">✅ Service Selesai pada: <?php echo $detail['tanggal_selesai']; ?> <br> Total: Rp
                            <?php echo number_format($detail['biaya_akhir'], 0, ',', '.'); ?></div>
                    <?php endif; ?>
                </div>
            </div>

        <?php else: ?>
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <h2>Daftar Service</h2>
                <a href="services.php?view=create" class="btn btn-blue">+ Input Service Baru</a>
            </div>

            <form method="GET" action="" style="margin:15px 0;">
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                    placeholder="Cari pelanggan/device..." style="display:inline-block; width:auto;">
                <button type="submit" class="btn btn-gray">Cari</button>
            </form>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tanggal</th>
                        <th>Pelanggan</th>
                        <th>Perangkat</th>
                        <th>Teknisi</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($services)): ?>
                        <tr>
                            <td colspan="7" style="text-align:center;">Tidak ada data service.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($services as $s): ?>
                            <tr>
                                <td><?php echo $s['id_service']; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($s['tanggal_masuk'])); ?></td>
                                <td><?php echo htmlspecialchars($s['nama_pelanggan']); ?></td>
                                <td><?php echo htmlspecialchars($s['nama_perangkat']); ?></td>
                                <td><?php echo htmlspecialchars($s['nama_teknisi'] ?? '-'); ?></td>
                                <td>
                                    <span
                                        style="background:#eee; padding:3px 8px; border-radius:4px; font-weight:bold; font-size:12px;">
                                        <?php echo htmlspecialchars($s['nama_status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="services.php?view=track&id=<?php echo $s['id_service']; ?>"
                                        class="btn btn-orange">Detail/Proses</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <div style="margin-top:20px;">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?view=list&page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"
                        style="padding:5px 10px; border:1px solid #ccc; text-decoration:none; <?php echo ($i == $page) ? 'background:#007bff; color:white;' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>

    </div>

    <?php include '../../views/layouts/footer.php'; ?>
</body>

</html>