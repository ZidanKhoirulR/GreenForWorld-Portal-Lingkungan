<?php
namespace App\Controllers;

use App\Models\KomentarBeritaModel;

class KomentarBeritaController {
    protected $model;

    public function __construct($f3) {
        $this->model = new KomentarBeritaModel($f3->get('DB'));
    }

    /** GET /komentar-berita/@berita_id — ambil daftar komentar (JSON) */
    public function list($f3, $params) {
        header('Content-Type: application/json');
        $berita_id = (int)$params['berita_id'];
        $komentar = $this->model->getByBeritaId($berita_id);

        foreach ($komentar as &$k) {
            $dt = new \DateTime($k['created_at']);
            $k['created_at_formatted'] = $dt->format('d M Y, H:i');
        }

        echo json_encode(['status' => 'ok', 'data' => $komentar]);
        exit;
    }

    /** POST /komentar-berita/add — simpan komentar baru */
    public function add($f3) {
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);

        $berita_id  = (int)($input['berita_id'] ?? 0);
        $parent_id  = isset($input['parent_id']) && $input['parent_id'] !== "" ? (int)$input['parent_id'] : null;
        $nama       = trim($input['nama'] ?? '');
        $email      = trim($input['email'] ?? '');
        $komentar   = trim($input['komentar'] ?? '');

        if (!$berita_id || !$nama || !$komentar) {
            echo json_encode(['status' => 'error', 'message' => 'Nama dan komentar wajib diisi.']);
            exit;
        }

        $nama     = htmlspecialchars($nama, ENT_QUOTES, 'UTF-8');
        $email    = filter_var($email, FILTER_SANITIZE_EMAIL);
        $komentar = htmlspecialchars($komentar, ENT_QUOTES, 'UTF-8');

        $this->model->add($berita_id, $nama, $email, $komentar, $parent_id);

        echo json_encode(['status' => 'ok', 'message' => 'Komentar berhasil ditambahkan.']);
        exit;
    }
}
