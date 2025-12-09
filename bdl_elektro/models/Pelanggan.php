<?php
// models/Pelanggan.php

class Pelanggan
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // 1. Ambil Semua Data (READ)
    public function getAll($limit, $offset, $search = '')
    {
        try {
            $sql = "SELECT * FROM pelanggan 
                    WHERE nama ILIKE :search 
                    OR email ILIKE :search 
                    OR no_hp ILIKE :search
                    ORDER BY id_pelanggan DESC 
                    LIMIT :limit OFFSET :offset";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':search', "%$search%");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    // Hitung total data
    public function countAll($search = '')
    {
        try {
            $sql = "SELECT COUNT(*) FROM pelanggan 
                    WHERE nama ILIKE :search 
                    OR email ILIKE :search 
                    OR no_hp ILIKE :search";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':search', "%$search%");
            $stmt->execute();

            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    // 2. Ambil 1 Data (Edit)
    public function getById($id)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM pelanggan WHERE id_pelanggan = :id");
            $stmt->execute(['id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }

    // 3. Tambah Data (CREATE) - INI YANG TADI ERROR
    public function create($nama, $no_hp, $alamat, $email, $password)
    {
        try {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            $sql = "INSERT INTO pelanggan (nama, no_hp, alamat, email, password, tanggal_daftar) 
                    VALUES (:nama, :no_hp, :alamat, :email, :password, NOW())";

            $stmt = $this->pdo->prepare($sql);

            return $stmt->execute([
                ':nama' => $nama,
                ':no_hp' => $no_hp,
                ':alamat' => $alamat,
                ':email' => $email,
                ':password' => $hashedPassword
            ]);

        } catch (PDOException $e) {
            // 🔥 HAPUS TANDA KOMENTAR (//) DI BAWAH INI:
            die("ERROR JELAS: " . $e->getMessage());

            return false;
        }
    }

    // 4. Update Data (UPDATE)
    public function update($id, $nama, $no_hp, $alamat, $email)
    {
        try {
            $sql = "UPDATE pelanggan 
                    SET nama = :nama, no_hp = :no_hp, alamat = :alamat, email = :email 
                    WHERE id_pelanggan = :id";

            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':id' => $id,
                ':nama' => $nama,
                ':no_hp' => $no_hp,
                ':alamat' => $alamat,
                ':email' => $email
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // 5. Hapus Data (DELETE)
    public function delete($id)
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM pelanggan WHERE id_pelanggan = :id");
            return $stmt->execute(['id' => $id]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>