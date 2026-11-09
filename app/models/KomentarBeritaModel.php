<?php
namespace App\Models;

class KomentarBeritaModel {
    protected $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /** Ambil semua komentar untuk berita tertentu */
    public function getByBeritaId($berita_id) {
        return $this->db->exec(
            "SELECT * FROM komentar_berita WHERE berita_id = ? ORDER BY created_at ASC",
            [(int)$berita_id]
        );
    }

    /** Hitung total komentar untuk berita tertentu */
    public function countByBeritaId($berita_id) {
        $result = $this->db->exec(
            "SELECT COUNT(*) AS total FROM komentar_berita WHERE berita_id = ?",
            [(int)$berita_id]
        );
        return $result[0]['total'] ?? 0;
    }

    /** Tambah komentar baru */
    public function add($berita_id, $nama, $email, $komentar, $parent_id = null) {
        $this->db->exec(
            "INSERT INTO komentar_berita (berita_id, parent_id, nama, email, komentar) VALUES (?, ?, ?, ?, ?)",
            [(int)$berita_id, $parent_id, $nama, $email, $komentar]
        );
    }
}
