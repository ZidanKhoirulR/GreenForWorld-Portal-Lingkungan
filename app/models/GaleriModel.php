<?php

namespace App\Models;

class GaleriModel {
    protected $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getAll() {
        return $this->db->exec("SELECT * FROM galeri ORDER BY tanggal DESC");
    }

    public function getPaginated($limit, $offset) {
        return $this->db->exec("SELECT * FROM galeri ORDER BY tanggal DESC LIMIT ?, ?", [$offset, $limit]);
    }

    public function countAll() {
        return $this->db->exec("SELECT COUNT(*) AS total FROM galeri")[0]['total'];
    }

    public function getByKategoriPaginated($kategori, $limit, $offset) {
        return $this->db->exec("SELECT * FROM galeri WHERE kategori = ? ORDER BY tanggal DESC LIMIT ?, ?", [$kategori, $offset, $limit]);
    }

    public function countByKategori($kategori) {
        return $this->db->exec("SELECT COUNT(*) AS total FROM galeri WHERE kategori = ?", [$kategori])[0]['total'];
    }

    public function getKategoriList() {
        return $this->db->exec("SELECT DISTINCT kategori FROM galeri ORDER BY kategori ASC");
    }
    
    public function insert($data) {
        $this->db->exec(
            "INSERT INTO galeri (judul, deskripsi, kategori, tanggal, gambar) VALUES (?, ?, ?, ?, ?)",
            [$data['judul'], $data['deskripsi'], $data['kategori'], $data['tanggal'], $data['gambar']]
        );
    }
    
    public function getById($id) {
        $result = $this->db->exec("SELECT * FROM galeri WHERE id = ?", [$id]);
        return $result ? $result[0] : null;
    }

    public function update($id, $data) {
        $this->db->exec(
            "UPDATE galeri SET judul = ?, deskripsi = ?, kategori = ?, tanggal = ?, gambar = ? WHERE id = ?",
            [$data['judul'], $data['deskripsi'], $data['kategori'], $data['tanggal'], $data['gambar'], $id]
        );
    }
    
    public function delete($id)
    {
        $this->db->exec("DELETE FROM galeri WHERE id = ?", [$id]);
    }

}