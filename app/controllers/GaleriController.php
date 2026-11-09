<?php

namespace App\Controllers;

class GaleriController
{
    protected $f3;
    protected $galeri;

    public function __construct($f3)
    {
        $this->f3 = $f3;
        $this->galeri = new \App\Models\GaleriModel($f3->get('DB'));
    }

    public function index()
    {
        $page = (int) $this->f3->get('GET.page') ?: 1;
        $limit = 3;
        $offset = ($page - 1) * $limit;
        $kategori = $this->f3->get('GET.kategori');

        if ($kategori) {
            $data = $this->galeri->getByKategoriPaginated($kategori, $limit, $offset);
            $total = $this->galeri->countByKategori($kategori);
        } else {
            $data = $this->galeri->getPaginated($limit, $offset);
            $total = $this->galeri->countAll();
        }

        $totalPages = ceil($total / $limit);
        $pages = range(1, $totalPages); // ? ini yang penting

        $this->f3->set('pages', $pages);

        $kategoriQuery = $kategori ? '&kategori=' . urlencode($kategori) : '';
        $kategoriList = $this->galeri->getKategoriList();

        $this->f3->set('galeri', $data);
        $this->f3->set('total', $total);
        $this->f3->set('page', $page);
        $this->f3->set('limit', $limit);
        $this->f3->set('kategori', $kategori);
        $this->f3->set('kategoriQuery', $kategoriQuery);
        $this->f3->set('totalPages', $totalPages);
        $this->f3->set('kategoriList', $kategoriList);

        echo \Template::instance()->render('gui-lieind/pages/galeri-list.html');
    }
}