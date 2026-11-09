<?php
namespace App\Controllers;

use App\Models\ArtikelModel;
use App\Models\KomentarModel;

class ArtikelController {
    protected $model;
    protected $komentarModel;

    public function __construct($f3) {
        $this->model = new ArtikelModel($f3->get('DB'));
        $this->komentarModel = new KomentarModel($f3->get('DB'));
    }

    public function index($f3) {
        // Ambil parameter halaman dari URL, default ke 1
        $page = (int)$f3->get('GET.page') ?: 1;
        $perPage = 5;
        $offset = ($page - 1) * $perPage;

        // Ambil artikel sesuai halaman
        $artikelList = $this->model->getPaginated($perPage, $offset);

        // Hitung total artikel untuk pagination
        $totalArtikel = $this->model->countPublik();
        $totalPages = ceil($totalArtikel / $perPage);

        // Format data artikel
        foreach ($artikelList as &$artikel) {
            $artikel['preview'] = substr($artikel['isi'], 0, 150);
            $datetime = new \DateTime($artikel['tanggal_diperbarui']);
            $artikel['tanggal_diperbarui'] = $datetime->format('F j, Y');
            $artikel['penulis'] = $artikel['penulis'] ?? 'Admin';
        }

        // Kirim ke template
        $f3->set('artikelList', $artikelList);
        $f3->set('currentPage', $page);
        $f3->set('totalPages', $totalPages);
        
        
        $f3->set('active_menu_1', '');
        $f3->set('active_menu_2', 'active');
        $f3->set('active_menu_3', '');
        $f3->set('active_menu_4', '');
        $f3->set('active_menu_5', '');
        $f3->set('active_menu_6', '');
        

        echo \Template::instance()->render('gui-lieind/pages/artikel-list.html');
    }

    public function detail($f3, $params) {
        $f3->set('_TITLE_', 'GREENFORWORLD');
        $f3->set('_SUB_TITLE_', 'Menyuarakan Kesadaran Lingkungan untuk Masa Depan Lebih Hijau');
        
        $slug = $params['slug'];
        $artikel = $this->model->getBySlug($slug);
        
        $datetime = new \DateTime($artikel['tanggal_diperbarui']);
        $artikel['tanggal_diperbarui'] = $datetime->format('F j, Y');   

        if (!$artikel) {
            $f3->error(404, 'Artikel tidak ditemukan');
            return;
        }

        $f3->set('artikel', $artikel);
        
        // Ambil komentar untuk artikel ini
        $komentarList = $this->komentarModel->getByArtikelId($artikel['id']);
        
        $komentarTree = [];
        $komentarById = [];
        
        // Initialize and format
        foreach ($komentarList as &$k) {
            $dt = new \DateTime($k['created_at']);
            $k['created_at_formatted'] = $dt->format('d M Y, H:i');
            $k['replies'] = []; // initialize replies array
            $komentarById[$k['id']] = &$k;
        }
        
        // Build tree structure
        foreach ($komentarList as &$k) {
            if (!empty($k['parent_id'])) {
                if (isset($komentarById[$k['parent_id']])) {
                    $komentarById[$k['parent_id']]['replies'][] = &$k;
                }
            } else {
                $komentarTree[] = &$k;
            }
        }
        
        $f3->set('komentarList', $komentarTree);
        $f3->set('totalKomentar', count($komentarList));

        echo \Template::instance()->render('gui-lieind/pages/artikel-detail.html');
    }
    
    
   function upload_image($f3) {

        header('Content-Type: application/json');

        if (!isset($_FILES['upload'])) {
            echo json_encode(['error' => 'File tidak ditemukan']);
            exit;
        }

        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/artikel/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $file = $_FILES['upload'];
        $ext  = pathinfo($file['name'], PATHINFO_EXTENSION);
        $name = uniqid('img_') . '.' . $ext;

        if (move_uploaded_file($file['tmp_name'], $uploadDir . $name)) {
            echo json_encode([
                'url' => '/uploads/artikel/' . $name
            ]);
        } else {
            echo json_encode([
                'error' => 'Gagal memindahkan file'
            ]);
        }
        exit;
    }

    
    
}