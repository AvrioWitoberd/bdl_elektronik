<?php
// views/admin/technicians.php
require_once '../../config/database.php';
require_once '../../models/Teknisi.php';

if (session_status() === PHP_SESSION_NONE) session_start();
// if (!isset($_SESSION['user_id'])) { header("Location: ../auth/login.php"); exit; } // Auth sementara mati

$teknisiModel = new Teknisi($pdo);
$view = $_GET['view'] ?? 'list';
$message = $_GET['msg'] ?? '';

// --- LOGIKA EDIT ---
$editData = null;
if ($view === 'edit') {
    if (!isset($_GET['id'])) { header("Location: technicians.php"); exit; }
    $editData = $teknisiModel->getById($_GET['id']);
    if (!$editData) die("Teknisi tidak ditemukan.");
}

// --- LOGIKA LIST ---
$teknisi = [];
$totalPages = 1;
$page = 1;
$search = '';
$activeOnly = false;

if ($view === 'list') {
    $limit = 5;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $limit;
    $search = $_GET['search'] ?? '';
    $activeOnly = isset($_GET['active']);

    $teknisi = $teknisiModel->getAll($limit, $offset, $search, $activeOnly);
    $totalTeknisi = $teknisiModel->countAll($search, $activeOnly);
    $totalPages = ceil($totalTeknisi / $limit);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kelola Teknisi</title>
    <style>
        body { font-family: sans-serif; padding: 20px; background-color: #f8f9fa; }
        .container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); max-width: 1000px; margin: auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #343a40; color: white; }
        .btn { padding: 8px 12px; color: white; text-decoration: none; border-radius: 4px; display: inline-block; border: none; cursor: pointer; }
        .btn-blue { background-color: #007bff; }
        .btn-green { background-color: #28a745; }
        .btn-red { background-color: #dc3545; }
        .btn-gray { background-color: #6c757d; }
        input, textarea { width: 100%; padding: 8px; margin: 5px 0 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .badge-active { background: #d4edda; color: #155724; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
        .badge-inactive { background: #f8d7da; color: #721c24; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
    </style>
</head>
<body>

<?php include '../../views/layouts/header.php'; ?>

<div class="container" style="margin-top:20px;">
    
    <?php if ($message): ?>
        <div style="background: #e2e3e5; padding: 10px; margin-bottom: 10px; border-radius: 4px;">Info: <?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <?php if ($view === 'create'): ?>
        <h2>Tambah Teknisi</h2>
        <form method="POST" action="../../controllers/TechnicianController.php">
            <input type="hidden" name="action" value="create">
            
            <label>Nama Teknisi:</label>
            <input type="text" name="nama_teknisi" required>

            <label>Keahlian (Spesialisasi):</label>
            <textarea name="keahlian"></textarea>

            <label>No HP:</label>
            <input type="text" name="no_hp">

            <label>Email:</label>
            <input type="email" name="email" required>

            <label>Password:</label>
            <input type="password" name="password" required>

            <label>Konfirmasi Password:</label>
            <input type="password" name="confirm_password" required>

            <label style="display:inline-flex; align-items:center;">
                <input type="checkbox" name="status_aktif" value="1" checked style="width:auto; margin-right:10px;"> Status Aktif
            </label><br><br>

            <button type="submit" class="btn btn-green">Simpan</button>
            <a href="technicians.php" class="btn btn-gray">Batal</a>
        </form>

    <?php elseif ($view === 'edit'): ?>
        <h2>Edit Teknisi</h2>
        <form method="POST" action="../../controllers/TechnicianController.php">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id_teknisi" value="<?php echo $editData['id_teknisi']; ?>">

            <label>Nama Teknisi:</label>
            <input type="text" name="nama_teknisi" value="<?php echo htmlspecialchars($editData['nama_teknisi']); ?>" required>

            <label>Keahlian:</label>
            <textarea name="keahlian"><?php echo htmlspecialchars($editData['keahlian']); ?></textarea>

            <label>No HP:</label>
            <input type="text" name="no_hp" value="<?php echo htmlspecialchars($editData['no_hp']); ?>">

            <label>Email:</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($editData['email']); ?>" required>

            <label style="display:inline-flex; align-items:center;">
                <input type="checkbox" name="status_aktif" value="1" <?php echo $editData['status_aktif'] ? 'checked' : ''; ?> style="width:auto; margin-right:10px;"> Status Aktif
            </label><br><br>

            <button type="submit" class="btn btn-blue">Update</button>
            <a href="technicians.php" class="btn btn-gray">Batal</a>
        </form>

    <?php else: ?>
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <h2>Daftar Teknisi</h2>
            <a href="technicians.php?view=create" class="btn btn-blue">+ Tambah Teknisi</a>
        </div>

        <form method="GET" action="" style="margin:15px 0;">
            <input type="text" name="search" placeholder="Cari nama/keahlian..." value="<?php echo htmlspecialchars($search); ?>" style="display:inline-block; width:auto;">
            <label><input type="checkbox" name="active" onchange="this.form.submit()" <?php echo $activeOnly ? 'checked' : ''; ?> style="width:auto;"> Hanya Aktif</label>
            <button type="submit" class="btn btn-gray">Cari</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama</th>
                    <th>Keahlian</th>
                    <th>Kontak</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($teknisi)): ?>
                    <tr><td colspan="6" style="text-align:center;">Data tidak ditemukan</td></tr>
                <?php else: ?>
                    <?php foreach ($teknisi as $t): ?>
                    <tr>
                        <td><?php echo $t['id_teknisi']; ?></td>
                        <td><?php echo htmlspecialchars($t['nama_teknisi']); ?></td>
                        <td><?php echo htmlspecialchars($t['keahlian']); ?></td>
                        <td>
                            <?php echo htmlspecialchars($t['email']); ?><br>
                            <small><?php echo htmlspecialchars($t['no_hp']); ?></small>
                        </td>
                        <td>
                            <span class="<?php echo $t['status_aktif'] ? 'badge-active' : 'badge-inactive'; ?>">
                                <?php echo $t['status_aktif'] ? 'Aktif' : 'Non-Aktif'; ?>
                            </span>
                        </td>
                        <td>
                            <a href="technicians.php?view=edit&id=<?php echo $t['id_teknisi']; ?>" class="btn btn-blue" style="font-size:12px;">Edit</a>
                            <a href="javascript:void(0);" onclick="if(confirm('Hapus?')) window.location.href='../../controllers/TechnicianController.php?action=delete&id=<?php echo $t['id_teknisi']; ?>'" class="btn btn-red" style="font-size:12px;">Hapus</a>
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