<?php
namespace App\Controllers;

use App\Models\ArtikelModel;

class TentangkamiController {
    protected $model;

    public function __construct($f3) {
        $this->model = new ArtikelModel($f3->get('DB'));
    }

    public function index($f3) {
        // Ambil parameter halaman dari URL, default ke 1
       
        
        $f3->set('active_menu_1', '');
        $f3->set('active_menu_2', '');
        $f3->set('active_menu_3', '');
        $f3->set('active_menu_4', '');
        $f3->set('active_menu_5', 'active');
        $f3->set('active_menu_6', '');
        

        echo \Template::instance()->render('gui-lieind/pages/tentang-kami.html');
    }

    public function detail($f3, $params) {
    
}
}