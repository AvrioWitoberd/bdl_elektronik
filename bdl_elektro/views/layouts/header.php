<?php
// views/layouts/header.php

// Pastikan session dimulai (jika belum)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Catatan:
// Karena file header ini di-include oleh file di dalam 'views/admin/',
// maka link href di bawah ini sifatnya relatif terhadap file induknya (misal: customers.php).
?>

<nav
    style="background-color: #343a40; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; color: white;">

    <div style="display: flex; align-items: center;">
        <a href="dashboard.php"
            style="color: white; text-decoration: none; font-weight: bold; font-size: 1.2rem; margin-right: 2rem;">
            🛠️ Admin Panel
        </a>

        <a href="dashboard.php" style="color: #ddd; text-decoration: none; margin-right: 1.5rem; transition: 0.3s;">
            📊 Dashboard
        </a>

        <a href="customers.php" style="color: #ddd; text-decoration: none; margin-right: 1.5rem;">
            👥 Pelanggan
        </a>

        <a href="technicians.php" style="color: #ddd; text-decoration: none; margin-right: 1.5rem;">
            🔧 Teknisi
        </a>

        <a href="sparepart.php" style="color: #ddd; text-decoration: none; margin-right: 1.5rem;">
            ⚙️ Spareparts
        </a>

        <a href="services.php" style="color: #ddd; text-decoration: none; margin-right: 1.5rem;">
            📋 Service
        </a>

        <a href="../reports/index.php" style="color: #ddd; text-decoration: none; margin-right: 1.5rem;">
            📈 Laporan
        </a>
    </div>

    <div>
        <span style="color: #bbb; margin-right: 1rem;">
            Halo, <?php echo htmlspecialchars($_SESSION['nama'] ?? 'Admin'); ?>
        </span>

        <a href="../../views/auth/logout.php"
            style="background-color: #dc3545; padding: 5px 10px; border-radius: 4px; color: white; text-decoration: none; font-size: 0.9rem;">
            Logout 🚪
        </a>
    </div>

</nav>

<style>
    nav a:hover {
        color: white !important;
        text-decoration: underline !important;
    }
</style>