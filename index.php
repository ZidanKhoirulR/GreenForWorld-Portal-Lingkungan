<?php
require __DIR__ . '/lib/base.php';

$f3 = \Base::instance();
session_start();

try {
    $db = new \DB\SQL('mysql:host=localhost;dbname=u793663043_greenworld', 'admin', 'admin123');
    $f3->set('DB', $db);
} catch (\PDOException $e) {
    die('Database error: ' . $e->getMessage());
}

$f3->set('DEBUG', 1);
$f3->config('config.cfg');
$f3->set('i_top_f3', 'i_top');
$f3->set('i_template_f3', 'i_template');
$f3->set('i_left_f3', 'i_left');
$f3->set('i_bottom_f3', 'i_bottom');
$f3->set('dashboard_f3', 'dashboard');


if (!isset($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
$f3->set('CSRF', $_SESSION['csrf']);


require_once 'app/controllers/MainController.php';
require_once 'app/controllers/AdminController.php';
require_once 'app/controllers/ArtikelController.php';
require_once 'app/controllers/BeritaController.php';
require_once 'app/models/UserModel.php';
require_once 'app/models/ArtikelModel.php';
require_once 'app/models/BeritaModel.php';
require_once 'app/controllers/KampanyeController.php';
require_once 'app/controllers/TentangkamiController.php';
require_once 'app/controllers/KontakController.php';
require_once 'app/controllers/LearnController.php';
require_once 'app/controllers/KomentarController.php';
require_once 'app/models/KomentarModel.php';
require_once 'app/controllers/KomentarBeritaController.php';
require_once 'app/models/KomentarBeritaModel.php';



$f3->route('GET /', function ($f3) {
    $controller = new \App\Controllers\MainController($f3);
    $controller->home($f3);
});


$f3->route('GET /news', 'App\Controllers\BeritaController->index');
$f3->route('GET /news/@slug', 'App\Controllers\BeritaController->detail');
$f3->route('GET /news[@page]', 'App\Controllers\BeritaController->index');
$f3->route('GET /news/more-news', 'App\Controllers\BeritaController->more');

$f3->route('GET /learn-more', 'App\Controllers\LearnController->index');

$f3->route('GET /article', 'App\Controllers\ArtikelController->index');
$f3->route('GET /artikel/@slug', 'App\Controllers\ArtikelController->detail');

$f3->route('GET /artikel[@page]', 'App\Controllers\ArtikelController->index');

// Komentar routes (Artikel)
$f3->route('GET /komentar/@artikel_id', 'App\Controllers\KomentarController->list');
$f3->route('POST /komentar/add', 'App\Controllers\KomentarController->add');

// Komentar routes (Berita)
$f3->route('GET /komentar-berita/@berita_id', 'App\Controllers\KomentarBeritaController->list');
$f3->route('POST /komentar-berita/add', 'App\Controllers\KomentarBeritaController->add');


$f3->route('GET|POST /admin/login', 'App\Controllers\AdminController->login');
$f3->route('GET /admin', 'App\Controllers\AdminController->dashboard');
//$f3->route('GET|POST /admin/artikel/edit/@id', 'App\Controllers\AdminController->edit');

//Content Start Here
$f3->route('GET /admin/daftar-artikel', 'App\Controllers\AdminController->daftar_artikel');
$f3->route('GET|POST /admin/artikel/add-new-post', 'App\Controllers\AdminController->add_artikel');
$f3->route('GET|POST /admin/artikel/edit/@id', 'App\Controllers\AdminController->edit_artikel');
$f3->route('POST /admin/artikel/hapus', 'App\Controllers\AdminController->hapus_artikel');
$f3->route(
  'POST /admin/artikel/upload-image',
  'App\Controllers\ArtikelController->upload_image'
);




$f3->route('GET /admin/daftar-berita', 'App\Controllers\AdminController->daftar_berita');
$f3->route('GET|POST /admin/berita/add-new-post-berita', 'App\Controllers\AdminController->add_berita');
$f3->route('GET|POST /admin/berita/edit/@id', 'App\Controllers\AdminController->edit_berita');
$f3->route('POST /admin/berita/hapus', 'App\Controllers\AdminController->hapus_berita');

require_once 'app/controllers/GaleriController.php';
require_once 'app/models/GaleriModel.php';
$f3->route('GET /gallery', 'App\Controllers\GaleriController->index');

$f3->route('GET /admin/gallery', 'App\Controllers\AdminController->daftar_galeri');
$f3->route('GET|POST /admin/gallery/add-new-post-gallery', 'App\Controllers\AdminController->add_galeri');
$f3->route('GET|POST /admin/gallery/edit/@id', 'App\Controllers\AdminController->edit_galeri');
$f3->route('POST /admin/gallery/hapus', 'App\Controllers\AdminController->hapus_galeri');


$f3->route('GET /campaign', 'App\Controllers\KampanyeController->index');
$f3->route('GET /about-us', 'App\Controllers\TentangkamiController->index');
$f3->route('GET /contact', 'App\Controllers\KontakController->index');

$f3->route('POST /contact/send', 'App\Controllers\KontakController->send');

$f3->route('GET /search', 'App\Controllers\SearchController->index');





$f3->run();