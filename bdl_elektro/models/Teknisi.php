<?php
// models/Teknisi.php

class Teknisi
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // 1. READ (Ambil Semua)
    public function getAll($limit, $offset, $search = '', $activeOnly = false)
    {
        try {
            $sql = "SELECT * FROM teknisi 
                    WHERE (nama_teknisi ILIKE :search OR email ILIKE :search OR keahlian ILIKE :search)";

            if ($activeOnly) {
                $sql .= " AND status_aktif = TRUE";
            }

            $sql .= " ORDER BY id_teknisi DESC LIMIT :limit OFFSET :offset";

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

    // Hitung Total Data
    public function countAll($search = '', $activeOnly = false)
    {
        try {
            $sql = "SELECT COUNT(*) FROM teknisi 
                    WHERE (nama_teknisi ILIKE :search OR email ILIKE :search OR keahlian ILIKE :search)";

            if ($activeOnly) {
                $sql .= " AND status_aktif = TRUE";
            }

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':search', "%$search%");
            $stmt->execute();

            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    // 2. GET BY ID (Untuk Edit)
    public function getById($id)
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM teknisi WHERE id_teknisi = :id");
            $stmt->execute(['id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }

    // 3. CREATE (Tambah)
    public function create($nama, $keahlian, $no_hp, $email, $password, $status_aktif)
    {
        try {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            $sql = "INSERT INTO teknisi (nama_teknisi, keahlian, no_hp, email, password, status_aktif, created_at) 
                    VALUES (:nama, :keahlian, :no_hp, :email, :password, :status, NOW())";

            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':nama' => $nama,
                ':keahlian' => $keahlian,
                ':no_hp' => $no_hp,
                ':email' => $email,
                ':password' => $hashedPassword,
                ':status' => $status_aktif ? 'true' : 'false' // Boolean Postgres
            ]);
        } catch (PDOException $e) {
            // DEBUG MODE: Tampilkan error jika kolom tidak ada / duplikat
            die("❌ ERROR TEKNISI CREATE: " . $e->getMessage());
        }
    }

    // 4. UPDATE
    public function update($id, $nama, $keahlian, $no_hp, $email, $status_aktif)
    {
        try {
            $sql = "UPDATE teknisi 
                    SET nama_teknisi = :nama, keahlian = :keahlian, no_hp = :no_hp, email = :email, status_aktif = :status 
                    WHERE id_teknisi = :id";

            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':id' => $id,
                ':nama' => $nama,
                ':keahlian' => $keahlian,
                ':no_hp' => $no_hp,
                ':email' => $email,
                ':status' => $status_aktif ? 'true' : 'false'
            ]);
        } catch (PDOException $e) {
            die("❌ ERROR TEKNISI UPDATE: " . $e->getMessage());
        }
    }

    // 5. DELETE
    public function delete($id)
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM teknisi WHERE id_teknisi = :id");
            return $stmt->execute(['id' => $id]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>