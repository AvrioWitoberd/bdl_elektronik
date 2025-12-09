<?php
// views/admin/customers.php

require_once '../../config/database.php';
require_once '../../models/Pelanggan.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pelangganModel = new Pelanggan($pdo);

$view = $_GET['view'] ?? 'list';
$message = $_GET['msg'] ?? '';

// --- LOGIKA READ ---
$editData = null;
if ($view === 'edit') {
    if (!isset($_GET['id'])) {
        header("Location: customers.php");
        exit;
    }
    $editData = $pelangganModel->getById($_GET['id']);
    if (!$editData)
        die("Customer not found.");
}

$pelanggan = [];
$totalPages = 1;
$page = 1;
$search = '';

if ($view === 'list') {
    $limit = 5;
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    $offset = ($page - 1) * $limit;
    $search = $_GET['search'] ?? '';

    $pelanggan = $pelangganModel->getAll($limit, $offset, $search);
    $totalPelanggan = $pelangganModel->countAll($search);
    $totalPages = ceil($totalPelanggan / $limit);
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Kelola Pelanggan</title>
    <style>
        body {
            font-family: sans-serif;
            padding: 20px;
            background-color: #f4f4f9;
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
            cursor: pointer;
            border: none;
            font-size: 14px;
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

        label {
            font-weight: bold;
        }
    </style>
</head>

<body>

    <?php include '../../views/layouts/header.php'; ?>

    <div class="container" style="margin-top:20px;">

        <?php if ($message): ?>
            <div style="background: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px; border-radius: 4px;">
                Info: <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($view === 'create'): ?>
            <h2>Tambah Pelanggan Baru</h2>
            <form method="POST" action="../../controllers/CustomerController.php">
                <input type="hidden" name="action" value="create">

                <label>Nama Lengkap:</label>
                <input type="text" name="nama" required>

                <label>No HP:</label>
                <input type="text" name="no_hp" required>

                <label>Email:</label>
                <input type="email" name="email" required>

                <label>Alamat:</label>
                <textarea name="alamat" rows="3"></textarea>

                <label>Password:</label>
                <input type="password" name="password" required>

                <label>Konfirmasi Password:</label>
                <input type="password" name="confirm_password" required>

                <button type="submit" class="btn btn-green">Simpan Data</button>
                <a href="customers.php" class="btn btn-gray">Batal</a>
            </form>

        <?php elseif ($view === 'edit'): ?>
            <h2>Edit Pelanggan</h2>
            <form method="POST" action="../../controllers/CustomerController.php">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id_pelanggan" value="<?php echo $editData['id_pelanggan']; ?>">

                <label>Nama Lengkap:</label>
                <input type="text" name="nama" value="<?php echo htmlspecialchars($editData['nama']); ?>" required>

                <label>No HP:</label>
                <input type="text" name="no_hp" value="<?php echo htmlspecialchars($editData['no_hp']); ?>" required>

                <label>Email:</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($editData['email']); ?>" required>

                <label>Alamat:</label>
                <textarea name="alamat" rows="3"><?php echo htmlspecialchars($editData['alamat']); ?></textarea>

                <button type="submit" class="btn btn-blue">Update Data</button>
                <a href="customers.php" class="btn btn-gray">Batal</a>
            </form>

        <?php else: ?>
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <h2>Daftar Pelanggan</h2>
                <a href="customers.php?view=create" class="btn btn-blue">+ Tambah Pelanggan</a>
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
                        <th>Nama</th>
                        <th>No HP</th>
                        <th>Email</th>
                        <th>Alamat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pelanggan)): ?>
                        <tr>
                            <td colspan="6" style="text-align:center;">Data tidak ditemukan.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($pelanggan as $p): ?>
                            <tr>
                                <td><?php echo $p['id_pelanggan']; ?></td>
                                <td><?php echo htmlspecialchars($p['nama']); ?></td>
                                <td><?php echo htmlspecialchars($p['no_hp']); ?></td>
                                <td><?php echo htmlspecialchars($p['email']); ?></td>
                                <td><?php echo htmlspecialchars($p['alamat']); ?></td>
                                <td>
                                    <a href="customers.php?view=edit&id=<?php echo $p['id_pelanggan']; ?>"
                                        class="btn btn-blue">Edit</a>
                                    <a href="javascript:void(0);"
                                        onclick="if(confirm('Hapus?')) window.location.href='../../controllers/CustomerController.php?action=delete&id=<?php echo $p['id_pelanggan']; ?>'"
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