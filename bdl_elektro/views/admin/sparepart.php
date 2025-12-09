<?php
// views/admin/sparepart.php

// 🔥 NYALAKAN ERROR DISPLAY
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../../config/database.php';
require_once '../../models/Sparepart.php';

if (session_status() === PHP_SESSION_NONE)
    session_start();
// if (!isset($_SESSION['user_id'])) { header("Location: ../auth/login.php"); exit; } 

$sparepartModel = new Sparepart($pdo);
$view = $_GET['view'] ?? 'list';
$message = $_GET['msg'] ?? '';

// --- LOGIKA EDIT ---
$editData = null;
if ($view === 'edit') {
    if (!isset($_GET['id'])) {
        echo "<script>window.location='sparepart.php';</script>";
        exit;
    }
    $editData = $sparepartModel->getById($_GET['id']);
    if (!$editData)
        die("Sparepart tidak ditemukan.");
}

// --- LOGIKA LIST ---
$spareparts = [];
$totalPages = 1;
$page = 1;
$search = '';

if ($view === 'list') {
    $limit = 5;
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    $offset = ($page - 1) * $limit;
    $search = $_GET['search'] ?? '';

    $spareparts = $sparepartModel->getAll($limit, $offset, $search);
    $total = $sparepartModel->countAll($search);
    $totalPages = ceil($total / $limit);
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Kelola Spareparts</title>
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
            margin-right: 5px;
        }

        .btn-blue {
            background-color: #007bff;
        }

        .btn-green {
            background-color: #28a745;
        }

        .btn-red {
            background-color: #dc3545;
        }

        .btn-gray {
            background-color: #6c757d;
        }

        input,
        textarea {
            width: 100%;
            padding: 8px;
            margin: 5px 0 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .alert {
            padding: 10px;
            background-color: #d4edda;
            color: #155724;
            margin-bottom: 15px;
            border-radius: 4px;
        }
    </style>
</head>

<body>

    <?php include '../../views/layouts/header.php'; ?>

    <div class="container" style="margin-top:20px;">

        <?php if ($message): ?>
            <div class="alert">Info: <?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if ($view === 'create'): ?>
            <h2>Tambah Sparepart</h2>
            <form method="POST" action="../../controllers/SparepartController.php">
                <input type="hidden" name="action" value="create">

                <label>Nama Sparepart:</label>
                <input type="text" name="nama_sparepart" required>

                <label>Merek:</label>
                <input type="text" name="merek">

                <label>Stok:</label>
                <input type="number" name="stok" value="0" required>

                <label>Harga (Rp):</label>
                <input type="number" name="harga" value="0" required>

                <button type="submit" class="btn btn-green">Simpan</button>
                <a href="sparepart.php" class="btn btn-gray">Batal</a>
            </form>

        <?php elseif ($view === 'edit'): ?>
            <h2>Edit Sparepart</h2>
            <form method="POST" action="../../controllers/SparepartController.php">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id_sparepart" value="<?php echo $editData['id_sparepart']; ?>">

                <label>Nama Sparepart:</label>
                <input type="text" name="nama_sparepart"
                    value="<?php echo htmlspecialchars($editData['nama_sparepart']); ?>" required>

                <label>Merek:</label>
                <input type="text" name="merek" value="<?php echo htmlspecialchars($editData['merek']); ?>">

                <label>Stok:</label>
                <input type="number" name="stok" value="<?php echo $editData['stok']; ?>" required>

                <label>Harga (Rp):</label>
                <input type="number" name="harga" value="<?php echo $editData['harga']; ?>" required>

                <button type="submit" class="btn btn-blue">Update</button>
                <a href="sparepart.php" class="btn btn-gray">Batal</a>
            </form>

        <?php else: ?>
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <h2>Daftar Spareparts</h2>
                <a href="sparepart.php?view=create" class="btn btn-blue">+ Tambah Barang</a>
            </div>

            <form method="GET" action="" style="margin:15px 0;">
                <input type="text" name="search" placeholder="Cari..." value="<?php echo htmlspecialchars($search); ?>"
                    style="display:inline-block; width:auto;">
                <button type="submit" class="btn btn-gray">Cari</button>
            </form>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Barang</th>
                        <th>Merek</th>
                        <th>Stok</th>
                        <th>Harga</th>
                        <th>Update Terakhir</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($spareparts)): ?>
                        <tr>
                            <td colspan="7" style="text-align:center;">Data tidak ditemukan</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($spareparts as $s): ?>
                            <tr>
                                <td><?php echo $s['id_sparepart']; ?></td>
                                <td><?php echo htmlspecialchars($s['nama_sparepart']); ?></td>
                                <td><?php echo htmlspecialchars($s['merek']); ?></td>
                                <td><?php echo $s['stok']; ?></td>
                                <td>Rp <?php echo number_format($s['harga'], 0, ',', '.'); ?></td>
                                <td><?php echo $s['tanggal_update'] ?? '-'; ?></td>
                                <td>
                                    <a href="sparepart.php?view=edit&id=<?php echo $s['id_sparepart']; ?>"
                                        class="btn btn-blue">Edit</a>
                                    <a href="javascript:void(0);"
                                        onclick="if(confirm('Hapus?')) window.location.href='../../controllers/SparepartController.php?action=delete&id=<?php echo $s['id_sparepart']; ?>'"
                                        class="btn btn-red">Hapus</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <div style="margin-top:20px;">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?view=list&page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"
                        style="padding:5px 10px; border:1px solid #ccc; text-decoration:none;">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>

        <?php endif; ?>

    </div>
    <?php include '../../views/layouts/footer.php'; ?>
</body>

</html>