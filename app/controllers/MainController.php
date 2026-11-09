<?php

namespace App\Controllers;
use App\Models\ArtikelModel;


class MainController {
    
    private $artikelModel;

    public function __construct($f3) {
        $this->artikelModel = new ArtikelModel($f3->get('DB'));
    }

    
    public function home($f3) {

    // Ambil 6 artikel untuk Featured
    $artikelFeatured = $this->artikelModel->getAll(6);

    // Ambil 3 artikel untuk Sidebar
    $artikelSidebar = $this->artikelModel->getAll(3);

    // Format Featured
    foreach ($artikelFeatured as &$artikel) {
        $artikel['preview'] = substr($artikel['isi'], 0, 150);

        $datetime = new \DateTime($artikel['tanggal_diperbarui']);
        $artikel['tanggal_diperbarui'] = $datetime->format('F j, Y');
    }

    // Format Sidebar (tanpa preview, lebih ringan)
    foreach ($artikelSidebar as &$artikel) {
        $datetime = new \DateTime($artikel['tanggal_diperbarui']);
        $artikel['tanggal_diperbarui'] = $datetime->format('F j, Y');
    }

    // Kirim ke View
    $f3->set('artikelFeatured', $artikelFeatured);
    $f3->set('artikelSidebar', $artikelSidebar);

    // Menu aktif
    $f3->set('active_menu_1', 'active');
    $f3->set('active_menu_2', '');
    $f3->set('active_menu_3', '');
    $f3->set('active_menu_4', '');
    $f3->set('active_menu_5', '');
    $f3->set('active_menu_6', '');

    echo \Template::instance()->render('gui-lieind/pages/home.html');
}

}
