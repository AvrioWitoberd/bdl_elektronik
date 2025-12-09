<?php
// models/Service.php

class Service
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // 1. GET ALL (List Data)
    public function getAll($limit, $offset, $search = '')
    {
        try {
            // Join lengkap ke pelanggan, teknisi, perangkat, status
            $sql = "SELECT s.*, 
                           p.nama as nama_pelanggan, 
                           t.nama_teknisi, 
                           d.model as nama_perangkat, 
                           d.jenis_perangkat,
                           sp.nama_status
                    FROM service s
                    LEFT JOIN perangkat d ON s.id_perangkat = d.id_perangkat
                    LEFT JOIN pelanggan p ON d.id_pelanggan = p.id_pelanggan
                    LEFT JOIN teknisi t ON s.id_teknisi = t.id_teknisi
                    LEFT JOIN status_perbaikan sp ON s.id_status = sp.id_status
                    WHERE p.nama ILIKE :search 
                       OR t.nama_teknisi ILIKE :search 
                       OR d.model ILIKE :search
                       OR s.keluhan ILIKE :search
                    ORDER BY s.tanggal_masuk DESC 
                    LIMIT :limit OFFSET :offset";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':search', "%$search%");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Uncomment baris ini jika data tidak tampil untuk melihat errornya
            // die("Error Get All: " . $e->getMessage());
            return [];
        }
    }

    // Hitung Total Data
    public function countAll($search = '')
    {
        try {
            $sql = "SELECT COUNT(s.id_service)
                    FROM service s
                    LEFT JOIN perangkat d ON s.id_perangkat = d.id_perangkat
                    LEFT JOIN pelanggan p ON d.id_pelanggan = p.id_pelanggan
                    LEFT JOIN teknisi t ON s.id_teknisi = t.id_teknisi
                    WHERE p.nama ILIKE :search OR d.model ILIKE :search";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':search', "%$search%");
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    // 2. GET BY ID (Detail)
    public function getById($id)
    {
        try {
            $sql = "SELECT s.*, 
                           p.nama as nama_pelanggan, p.no_hp as hp_pelanggan,
                           t.nama_teknisi, t.id_teknisi,
                           d.model as nama_perangkat, d.merek, d.serial_number,
                           sp.nama_status
                    FROM service s
                    LEFT JOIN perangkat d ON s.id_perangkat = d.id_perangkat
                    LEFT JOIN pelanggan p ON d.id_pelanggan = p.id_pelanggan
                    LEFT JOIN teknisi t ON s.id_teknisi = t.id_teknisi
                    LEFT JOIN status_perbaikan sp ON s.id_status = sp.id_status
                    WHERE s.id_service = :id";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }

    // 3. CREATE (Simpan Baru)
    public function create($id_perangkat, $id_teknisi, $keluhan, $biaya_estimasi)
    {
        try {
            // ID Status 1 = Menunggu (Pastikan tabel status_perbaikan sudah diisi SQL Langkah 0)
            $defaultStatus = 1;

            // Pastikan id_teknisi NULL jika kosong (bukan string kosong/0)
            $teknisiValue = empty($id_teknisi) ? null : $id_teknisi;

            $sql = "INSERT INTO service (id_perangkat, id_teknisi, id_status, keluhan, biaya_estimasi, tanggal_masuk) 
                    VALUES (:id_perangkat, :id_teknisi, :id_status, :keluhan, :biaya_estimasi, NOW())";

            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':id_perangkat' => $id_perangkat,
                ':id_teknisi' => $teknisiValue,
                ':id_status' => $defaultStatus,
                ':keluhan' => $keluhan,
                ':biaya_estimasi' => $biaya_estimasi
            ]);
        } catch (PDOException $e) {
            // 🔥 TAMPILKAN ERROR JIKA GAGAL
            die("❌ GAGAL CREATE SERVICE: " . $e->getMessage());
        }
    }

    // 4. UPDATE STATUS
    public function updateStatus($id_service, $id_status, $catatan_internal)
    {
        try {
            // Update status (Kita abaikan catatan internal dulu jika tidak ada kolomnya di DB)
            // Jika ada kolom catatan, tambahkan di query
            $sql = "UPDATE service SET id_status = :id_status WHERE id_service = :id";

            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':id' => $id_service,
                ':id_status' => $id_status
            ]);
        } catch (PDOException $e) {
            die("❌ GAGAL UPDATE STATUS: " . $e->getMessage());
        }
    }

    // 5. COMPLETE (Selesaikan)
    public function completeService($id_service, $biaya_akhir)
    {
        try {
            $statusSelesai = 4; // Pastikan ID 4 ada di DB (Selesai)

            $sql = "UPDATE service 
                    SET id_status = :id_status, 
                        biaya_akhir = :biaya_akhir, 
                        tanggal_selesai = NOW() 
                    WHERE id_service = :id";

            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':id' => $id_service,
                ':id_status' => $statusSelesai,
                ':biaya_akhir' => $biaya_akhir
            ]);
        } catch (PDOException $e) {
            die("❌ GAGAL COMPLETE SERVICE: " . $e->getMessage());
        }
    }
}
?>