<?php
namespace App\Models;

class ArtikelModel {
    protected $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getAll($limit = null) {
        $sql = "SELECT * FROM artikel WHERE status = 'publik' ORDER BY tanggal_dibuat DESC";
        if ($limit !== null) {
            $sql .= " LIMIT ?";
            return $this->db->exec($sql, [$limit]);
        }
        return $this->db->exec($sql);
    }

    public function getBySlug($slug) {
        return $this->db->exec("SELECT * FROM artikel WHERE slug = ?", [$slug])[0] ?? null;
    }
    
    public function getPaginated($limit, $offset) {
        return $this->db->exec(
            "SELECT judul, slug, gambar, isi, penulis, tanggal_diperbarui FROM artikel WHERE status = 'publik' ORDER BY tanggal_diperbarui DESC LIMIT ? OFFSET ?",
            [$limit, $offset]
        );
    }

    public function countPublik() {
        return $this->db->exec("SELECT COUNT(*) AS total FROM artikel WHERE status = 'publik'")[0]['total'];
    }
    
    public function getById($id) {
        return $this->db->exec("SELECT * FROM artikel WHERE id = ?", [$id])[0] ?? null;
    }

    
    public function slugExists($slug)
    {
        return $this->db->exec("SELECT id FROM artikel WHERE slug = ?", [$slug]);
    }

    private function generateSlug($judul)
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $judul), '-'));
        return $slug;
    }

    public function create($judul, $isi, $status)
    {
        $slug = $this->generateSlug($judul);
        if ($this->slugExists($slug)) {
            $slug .= '-' . time(); // tambahkan timestamp agar unik
        }
        
        $sql = "INSERT INTO artikel (judul, slug, isi, status) VALUES (?, ?, ?, ?)";
        $this->db->exec($sql, [$judul, $slug, $isi, $status]);
    }
    
    public function update($id, $judul, $isi, $status)
    {
        $sql = "UPDATE artikel SET judul = ?, isi = ?, status = ?, tanggal_diperbarui = NOW() WHERE id = ?";
        $this->db->exec($sql, [$judul, $isi, $status, $id]);
    }
    
    public function delete($id)
    {
        $this->db->exec("DELETE FROM artikel WHERE id = ?", [$id]);
    }

}