<?php
namespace App\Models;

class KomentarModel {
    protected $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /** Ambil semua komentar untuk artikel tertentu */
    public function getByArtikelId($artikel_id) {
        return $this->db->exec(
            "SELECT * FROM komentar WHERE artikel_id = ? ORDER BY created_at ASC",
            [(int)$artikel_id]
        );
    }

    /** Hitung total komentar untuk artikel tertentu */
    public function countByArtikelId($artikel_id) {
        $result = $this->db->exec(
            "SELECT COUNT(*) AS total FROM komentar WHERE artikel_id = ?",
            [(int)$artikel_id]
        );
        return $result[0]['total'] ?? 0;
    }

    /** Tambah komentar baru */
    public function add($artikel_id, $nama, $email, $komentar, $parent_id = null) {
        $this->db->exec(
            "INSERT INTO komentar (artikel_id, parent_id, nama, email, komentar) VALUES (?, ?, ?, ?, ?)",
            [(int)$artikel_id, $parent_id, $nama, $email, $komentar]
        );
    }
}
