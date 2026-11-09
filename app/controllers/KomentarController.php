<?php
namespace App\Controllers;

use App\Models\KomentarModel;

class KomentarController {
    protected $model;

    public function __construct($f3) {
        $this->model = new KomentarModel($f3->get('DB'));
    }

    /** GET /komentar/@artikel_id — ambil daftar komentar (JSON) */
    public function list($f3, $params) {
        header('Content-Type: application/json');
        $artikel_id = (int)$params['artikel_id'];
        $komentar = $this->model->getByArtikelId($artikel_id);

        // Format tanggal agar lebih mudah dibaca
        foreach ($komentar as &$k) {
            $dt = new \DateTime($k['created_at']);
            $k['created_at_formatted'] = $dt->format('d M Y, H:i');
        }

        echo json_encode(['status' => 'ok', 'data' => $komentar]);
        exit;
    }

    /** POST /komentar/add — simpan komentar baru */
    public function add($f3) {
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);

        $artikel_id = (int)($input['artikel_id'] ?? 0);
        $parent_id  = isset($input['parent_id']) && $input['parent_id'] !== "" ? (int)$input['parent_id'] : null;
        $nama       = trim($input['nama'] ?? '');
        $email      = trim($input['email'] ?? '');
        $komentar   = trim($input['komentar'] ?? '');

        // Validasi
        if (!$artikel_id || !$nama || !$komentar) {
            echo json_encode(['status' => 'error', 'message' => 'Nama dan komentar wajib diisi.']);
            exit;
        }

        // Sanitasi dasar
        $nama     = htmlspecialchars($nama, ENT_QUOTES, 'UTF-8');
        $email    = filter_var($email, FILTER_SANITIZE_EMAIL);
        $komentar = htmlspecialchars($komentar, ENT_QUOTES, 'UTF-8');

        $this->model->add($artikel_id, $nama, $email, $komentar, $parent_id);

        echo json_encode(['status' => 'ok', 'message' => 'Komentar berhasil ditambahkan.']);
        exit;
    }
}
