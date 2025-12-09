<?php
// models/Sparepart.php

class Sparepart
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // 1. READ (Ambil Semua)
    public function getAll($limit, $offset, $search = '')
    {
        try {
            // ILIKE = Case insensitive search di PostgreSQL
            $sql = "SELECT * FROM sparepart 
                    WHERE nama_sparepart ILIKE :search 
                    OR merek ILIKE :search
                    ORDER BY id_sparepart DESC 
                    LIMIT :limit OFFSET :offset";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':search', "%$search%");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Jika error query, return array kosong biar web tidak crash
            return [];
        }
    }

    // Hitung Total Data (Untuk Pagination)
    public function countAll($search = '')
    {
        try {
            $sql = "SELECT COUNT(*) FROM sparepart 
                    WHERE nama_sparepart ILIKE :search 
                    OR merek ILIKE :search";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':search', "%$search%");
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    // 2. GET BY ID (Untuk Form Edit)
    public function getById($id)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM sparepart WHERE id_sparepart = :id");
            $stmt->execute(['id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }

    // 3. CREATE (Tambah Data)
    public function create($nama, $stok, $harga, $merek)
    {
        try {
            // Pastikan kolom 'tanggal_update' ada di database. 
            // Jika tidak ada, hapus bagian: , tanggal_update / , NOW()
            $sql = "INSERT INTO sparepart (nama_sparepart, stok, harga, merek, tanggal_update) 
                    VALUES (:nama, :stok, :harga, :merek, NOW())";

            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':nama' => $nama,
                ':stok' => $stok,
                ':harga' => $harga,
                ':merek' => $merek
            ]);
        } catch (PDOException $e) {
            // Tampilkan error biar tidak blank
            die("❌ ERROR CREATE SPAREPART: " . $e->getMessage());
        }
    }

    // 4. UPDATE (Edit Data)
    public function update($id, $nama, $stok, $harga, $merek)
    {
        try {
            $sql = "UPDATE sparepart 
                    SET nama_sparepart = :nama, stok = :stok, harga = :harga, merek = :merek, tanggal_update = NOW() 
                    WHERE id_sparepart = :id";

            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':id' => $id,
                ':nama' => $nama,
                ':stok' => $stok,
                ':harga' => $harga,
                ':merek' => $merek
            ]);
        } catch (PDOException $e) {
            die("❌ ERROR UPDATE SPAREPART: " . $e->getMessage());
        }
    }

    // 5. DELETE (Hapus Data)
    public function delete($id)
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM sparepart WHERE id_sparepart = :id");
            return $stmt->execute(['id' => $id]);
        } catch (PDOException $e) {
            die("❌ ERROR DELETE SPAREPART: " . $e->getMessage());
        }
    }
}
?>