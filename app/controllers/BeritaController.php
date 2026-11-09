<?php
namespace App\Controllers;

use App\Models\BeritaModel;
use App\Models\KomentarBeritaModel;

class BeritaController {
    protected $model;
    protected $komentarModel;

    public function __construct($f3) {
        $this->model = new BeritaModel($f3->get('DB'));
        $this->komentarModel = new KomentarBeritaModel($f3->get('DB'));
    }

    public function index($f3) {
        // Ambil parameter halaman dari URL, default ke 1
        $page = (int)$f3->get('GET.page') ?: 1;
        $perPage = 5;
        $offset = ($page - 1) * $perPage;

        // Ambil Berita sesuai halaman
        $BeritaList = $this->model->getPaginated($perPage, $offset);

        // Hitung total Berita untuk pagination
        $totalBerita = $this->model->countPublik();
        $totalPages = ceil($totalBerita / $perPage);

        // Format data Berita
        foreach ($BeritaList as &$Berita) {
            $Berita['preview'] = substr($Berita['isi'], 0, 150);
            $datetime = new \DateTime($Berita['tanggal_diperbarui']);
            $Berita['tanggal_diperbarui'] = $datetime->format('F j, Y');
            $Berita['kategori'] = !empty($Berita['kategori']) ? $Berita['kategori'] : 'Umum';

        }

        // Kirim ke template
        $f3->set('BeritaList', $BeritaList);
        $f3->set('currentPage', $page);
        $f3->set('totalPages', $totalPages);
        
        
        $f3->set('active_menu_1', '');
        $f3->set('active_menu_2', 'active');
        $f3->set('active_menu_3', '');
        $f3->set('active_menu_4', '');
        $f3->set('active_menu_5', '');
        $f3->set('active_menu_6', '');
        

        echo \Template::instance()->render('gui-lieind/pages/berita-list.html');
    }

    public function detail($f3, $params) {
    $f3->set('_TITLE_', 'GREENFORWORLD');
    $f3->set('_SUB_TITLE_', 'Menyuarakan Kesadaran Lingkungan untuk Masa Depan Lebih Hijau');
    
    $slug = $params['slug'];
    $Berita = $this->model->getBySlug($slug);

    if (!$Berita) {
        $f3->error(404, 'Berita tidak ditemukan');
        return;
    }

    $datetime = new \DateTime($Berita['tanggal_diperbarui']);
    $Berita['tanggal_diperbarui'] = $datetime->format('F j, Y');   

    // pakai huruf kecil
    $f3->set('berita', $Berita);

    // Ambil komentar untuk berita ini
    $komentarList = $this->komentarModel->getByBeritaId($Berita['id']);

    $komentarTree = [];
    $komentarById = [];

    // Initialize and format
    foreach ($komentarList as &$k) {
        $dt = new \DateTime($k['created_at']);
        $k['created_at_formatted'] = $dt->format('d M Y, H:i');
        $k['replies'] = [];
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

    echo \Template::instance()->render('gui-lieind/pages/berita-detail.html');
}


public function more($f3) {
        // Ambil parameter halaman dari URL, default ke 1
        $page = (int)$f3->get('GET.page') ?: 1;
        $perPage = 5;
        $offset = ($page - 1) * $perPage;

        // Ambil Berita sesuai halaman
        $BeritaList = $this->model->getPaginated($perPage, $offset);

        // Hitung total Berita untuk pagination
        $totalBerita = $this->model->countPublik();
        $totalPages = ceil($totalBerita / $perPage);

        // Format data Berita
        foreach ($BeritaList as &$Berita) {
            $Berita['preview'] = substr($Berita['isi'], 0, 150);
            $datetime = new \DateTime($Berita['tanggal_diperbarui']);
            $Berita['tanggal_diperbarui'] = $datetime->format('F j, Y');
        }

        // Kirim ke template
        $f3->set('BeritaList', $BeritaList);
        $f3->set('currentPage', $page);
        $f3->set('totalPages', $totalPages);
        
        
        $f3->set('active_menu_1', '');
        $f3->set('active_menu_2', 'active');
        $f3->set('active_menu_3', '');
        $f3->set('active_menu_4', '');
        $f3->set('active_menu_5', '');
        $f3->set('active_menu_6', '');
        

        echo \Template::instance()->render('gui-lieind/pages/more-berita.html');
    }


}