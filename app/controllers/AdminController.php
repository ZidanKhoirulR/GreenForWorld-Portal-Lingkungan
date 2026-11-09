<?php

namespace App\Controllers;

use App\Models\ArtikelModel;
use App\Models\BeritaModel;
use App\Models\GaleriModel;

class AdminController
{
    // Method proteksi admin
    private function checkAdmin($f3)
    {
        if (!$f3->get('SESSION.admin')) {
            $f3->reroute('/admin/login');
            exit;
        }
    }

    public function login($f3)
    {
        // WAJIB: inisialisasi default
        if (!$f3->exists('error')) {
            $f3->set('error', null);
        }

        if (!$f3->exists('SESSION.csrf')) {
            $f3->set('SESSION.csrf', bin2hex(random_bytes(32)));
        }

        if ($f3->get('VERB') === 'POST') {
            $login = trim($f3->get('POST.username'));
            $password = $f3->get('POST.password');
            $csrf = $f3->get('POST.csrf');

            if ($csrf !== $f3->get('SESSION.csrf')) {
                $f3->set('error', 'Permintaan tidak valid (CSRF)');
            } else {
                $userModel = new \App\Models\UserModel($f3->get('DB'));
                $user = $userModel->getByLogin($login);

                if ($user && password_verify($password, $user['password'])) {
                    session_regenerate_id(true);
                    $f3->set('SESSION.admin', true);
                    $f3->set('SESSION.nama_user', $user['nama_user']);
                    $f3->set('SESSION.last_active', time());
                    $f3->reroute('/admin');
                } else {
                    $f3->set('error', 'Login Gagal: Username atau Password salah');
                }
            }
        }

        echo \Template::instance()->render('gui-lieind/pages/admin/login.html');
    }

    // Method untuk inisialisasi variabel template admin yang wajib ada
    private function init_admin_page($f3)
    {
        $vars = [
            'active_menu_dashboard_f3',
            'active_menu_daftarartikel_f3',
            'active_menu_daftarberita_f3',
            'active_menu_daftargaleri_f3',
            'active_menu_addnewartikel_f3'
        ];
        
        foreach ($vars as $var) {
            $f3->set($var, '');
        }
        
        if (!$f3->exists('error')) {
            $f3->set('error', null);
        }
        
        $f3->set('CSRF', $f3->get('SESSION.csrf'));
    }

    public function dashboard($f3)
    {
        // ?? Proteksi akses: hanya admin yang login bisa masuk
        if (!$f3->get('SESSION.admin')) {
            $f3->reroute('/admin/login');
            return;
        }

        $this->init_admin_page($f3);
        $f3->set('active_menu_dashboard_f3', 'active');
        
        echo \Template::instance()->render('gui-lieind/pages/admin/Template_dashboard.html');
    }
    
    private function sanitize_html($html)
    {
        return strip_tags($html, '<p><b><i><u><strong><em><ul><ol><li><br>');
    }

    public function daftar_artikel($f3)
    {
        $this->checkAdmin($f3);
        $this->init_admin_page($f3);
        $f3->set('active_menu_daftarartikel_f3', 'active');

        $db = $f3->get('DB');
        $limit = 10; // increase limit for better list
        $page = (int) $f3->get('GET.page') ?: 1;
        $offset = ($page - 1) * $limit;

        $total = $db->exec('SELECT COUNT(*) AS total FROM artikel')[0]['total'];
        $total_pages = ceil($total / $limit);

        $artikel = $db->exec(
            'SELECT id, judul, penulis, tanggal_dibuat, status FROM artikel ORDER BY tanggal_dibuat DESC LIMIT ? OFFSET ?',
            [$limit, $offset]
        );

        $f3->set('total_artikel', $total);
        $f3->set('artikel_list', $artikel);
        $f3->set('current_page', $page);
        $f3->set('total_pages', $total_pages);

        $f3->set('template', 'gui-lieind/pages/admin/i_template_tabel.html');
        echo \Template::instance()->render('gui-lieind/pages/admin/Template_tabel_daftar_artikel.html');
    }


    public function add_artikel($f3)
    {
        $this->checkAdmin($f3);

        if ($f3->get('VERB') === 'POST') {
            $csrf = $f3->get('POST.csrf');
            if ($csrf !== $f3->get('CSRF')) {
                $f3->set('error', 'Token CSRF tidak valid');
                return;
            }

            $judul = $f3->get('POST.judul');
            $isi = $f3->get('POST.isi');
            $isi = $this->sanitize_html($isi);
            $status = $f3->get('POST.status') === '1' ? 'publik' : 'draft';

            // Set ke template agar tetap muncul di form saat error
            $f3->set('judul', $judul);
            $f3->set('isi', $isi);

            // Validasi kosong
            if (empty($judul) || empty($isi)) {
                $f3->set('error', 'Judul dan isi wajib diisi');
                return;
            }

            // Validasi panjang
            if (strlen($judul) < 10 || strlen($isi) < 200) {
                $f3->set('error', 'Judul minimal 5 karakter dan isi minimal 20 karakter');
                return;
            }

            // Simpan ke DB
            $model = new ArtikelModel($f3->get('DB'));
            $model->create($judul, $isi, $status);
            $f3->reroute('/admin/daftar-artikel');
            return;
        }



        $this->init_admin_page($f3);
        $f3->set('active_menu_daftarartikel_f3', 'active');

        // Initialize variables for template
        if (!$f3->exists('judul')) $f3->set('judul', '');
        if (!$f3->exists('isi')) $f3->set('isi', '');

        // Jika GET, tampilkan form
        $f3->set('template', 'gui-lieind/pages/admin/i_template_form.html');
        echo \Template::instance()->render('gui-lieind/pages/admin/Template_add-new-post.html');
    }


    public function edit_artikel($f3)
    {
       
        $this->checkAdmin($f3);
        $id = $f3->get('PARAMS.id');
        $f3->set('id', $id);

        $model = new ArtikelModel($f3->get('DB'));
        $artikel = $model->getById($id);

        if (!$artikel) {
            $f3->error(404, 'Artikel tidak ditemukan');
            return;
        }

        if ($f3->get('VERB') === 'POST') {
            $csrf = $f3->get('POST.csrf');
            if ($csrf !== $f3->get('CSRF')) {
                $f3->set('error', 'Token CSRF tidak valid');
                return;
            }

            $judul = $f3->get('POST.judul');
            $isi = $this->sanitize_html($f3->get('POST.isi'));
            $status = $f3->get('POST.status') === '1' ? 'publik' : 'draft';

            $f3->set('judul', $judul);
            $f3->set('isi', $isi);

            if (empty($judul) || empty($isi)) {
                $f3->set('error', 'Judul dan isi wajib diisi');
                return;
            }

            if (strlen($judul) < 10 || strlen($isi) < 200) {
                $f3->set('error', 'Judul minimal 10 karakter dan isi minimal 200 karakter');
                return;
            }
 
            $model->update($id, $judul, $isi, $status);
            $f3->reroute('/admin/daftar-artikel');
            return;
        }

        // Isi form dengan data lama
        $f3->set('judul', $artikel['judul']);
        $f3->set('isi', $artikel['isi']);
        $f3->set('status', $artikel['status'] === 'publik' ? '1' : '0');

        $this->checkAdmin($f3);
        $this->init_admin_page($f3);
        $f3->set('active_menu_daftarartikel_f3', 'active');

        $f3->set('template', 'gui-lieind/pages/admin/i_template_form_edit.html');
        echo \Template::instance()->render('gui-lieind/pages/admin/Template_add-new-post.html');
    }
    
    public function hapus_artikel($f3)
    {
        $this->checkAdmin($f3);

        $id = $f3->get('POST.id');
        $model = new ArtikelModel($f3->get('DB'));
        $model->delete($id);

        $f3->reroute('/admin/daftar-artikel');
    }
    
    public function daftar_berita($f3)
    {
        $this->checkAdmin($f3);
        $this->init_admin_page($f3);
        $f3->set('active_menu_daftarberita_f3', 'active');

        $db = $f3->get('DB');
        $limit = 10;
        $page = (int) $f3->get('GET.page') ?: 1;
        $offset = ($page - 1) * $limit;

        $total = $db->exec('SELECT COUNT(*) AS total FROM tb_news_wl')[0]['total'];
        $total_pages = ceil($total / $limit);

        $berita = $db->exec(
            'SELECT id, judul, penulis, tanggal_dibuat, status FROM tb_news_wl ORDER BY tanggal_dibuat DESC LIMIT ? OFFSET ?',
            [$limit, $offset]
        );

        $f3->set('total_berita', $total);
        $f3->set('berita_list', $berita);
        $f3->set('current_page', $page);
        $f3->set('total_pages', $total_pages);

        $f3->set('template', 'gui-lieind/pages/admin/i_template_tabel_berita.html');
        echo \Template::instance()->render('gui-lieind/pages/admin/Template_tabel_daftar_berita.html');
    }


    public function add_berita($f3)
    {
        $this->checkAdmin($f3);

        if ($f3->get('VERB') === 'POST') {
            $csrf = $f3->get('POST.csrf');
            if ($csrf !== $f3->get('CSRF')) {
                $f3->set('error', 'Token CSRF tidak valid');
                return;
            }

            $judul = $f3->get('POST.judul');
            $isi = $f3->get('POST.isi');
            $isi = $this->sanitize_html($isi);
            $status = $f3->get('POST.status') === '1' ? 'publik' : 'draft';

            // Set ke template agar tetap muncul di form saat error
            $f3->set('judul', $judul);
            $f3->set('isi', $isi);

            // Validasi kosong
            if (empty($judul) || empty($isi)) {
                $f3->set('error', 'Judul dan isi wajib diisi');
                return;
            }

            // Validasi panjang
            if (strlen($judul) < 10 || strlen($isi) < 200) {
                $f3->set('error', 'Judul minimal 5 karakter dan isi minimal 20 karakter');
                return;
            }

            // Simpan ke DB
            $model = new beritaModel($f3->get('DB'));
            $model->create($judul, $isi, $status);
            $f3->reroute('/admin/daftar-berita');
            return;
        }



        $this->init_admin_page($f3);
        $f3->set('active_menu_daftarberita_f3', 'active');

        // Initialize variables for template
        if (!$f3->exists('judul')) $f3->set('judul', '');
        if (!$f3->exists('isi')) $f3->set('isi', '');

        // Jika GET, tampilkan form
        $f3->set('template', 'gui-lieind/pages/admin/i_template_form_berita.html');
        echo \Template::instance()->render('gui-lieind/pages/admin/Template_add-new-post-berita.html');
    }


    public function edit_berita($f3)
    {
        $this->checkAdmin($f3);
        $id = $f3->get('PARAMS.id');
        $f3->set('id', $id);

        $model = new BeritaModel($f3->get('DB'));
        $berita = $model->getById($id);

        if (!$berita) {
            $f3->error(404, 'Berita tidak ditemukan');
            return;
        }

        if ($f3->get('VERB') === 'POST') {
            $csrf = $f3->get('POST.csrf');
            if ($csrf !== $f3->get('CSRF')) {
                $f3->set('error', 'Token CSRF tidak valid');
                return;
            }

            $judul = $f3->get('POST.judul');
            $isi = $this->sanitize_html($f3->get('POST.isi'));
            $status = $f3->get('POST.status') === '1' ? 'publik' : 'draft';

            $f3->set('judul', $judul);
            $f3->set('isi', $isi);

            if (empty($judul) || empty($isi)) {
                $f3->set('error', 'Judul dan isi wajib diisi');
                return;
            }

            if (strlen($judul) < 10 || strlen($isi) < 200) {
                $f3->set('error', 'Judul minimal 10 karakter dan isi minimal 200 karakter');
                return;
            }

            $model->update($id, $judul, $isi, $status);
            $f3->reroute('/admin/daftar-berita');
            return;
        }

        // Isi form dengan data lama
        $f3->set('judul', $berita['judul']);
        $f3->set('isi', $berita['isi']);
        $f3->set('status', $berita['status'] === 'publik' ? '1' : '0');

        $this->init_admin_page($f3);
        $f3->set('active_menu_daftarberita_f3', 'active');

        $f3->set('template', 'gui-lieind/pages/admin/i_template_form_edit_berita.html');
        echo \Template::instance()->render('gui-lieind/pages/admin/Template_add-new-post-berita.html');
    }
    
    public function hapus_berita($f3)
    {
        $this->checkAdmin($f3);

        $id = $f3->get('POST.id');
        $model = new BeritaModel($f3->get('DB'));
        $model->delete($id);

        $f3->reroute('/admin/daftar-berita');
    }
    
    
    public function daftar_galeri($f3)
    {
        $this->checkAdmin($f3);
        $this->init_admin_page($f3);
        $f3->set('active_menu_daftargaleri_f3', 'active');

        $db = $f3->get('DB');
        $limit = 10;
        $page = (int) $f3->get('GET.page') ?: 1;
        $offset = ($page - 1) * $limit;

        $total = $db->exec('SELECT COUNT(*) AS total FROM galeri')[0]['total'];
        $total_pages = ceil($total / $limit);

        $galeri = $db->exec(
            'SELECT id, judul, deskripsi, gambar, kategori, tanggal, created_at FROM galeri ORDER BY tanggal DESC LIMIT ? OFFSET ?',
            [$limit, $offset]
        );

        $f3->set('total_galeri', $total);
        $f3->set('galeri_list', $galeri);
        $f3->set('current_page', $page);
        $f3->set('total_pages', $total_pages);

        $f3->set('template', 'gui-lieind/pages/admin/i_template_tabel_galeri.html');
        echo \Template::instance()->render('gui-lieind/pages/admin/Template_tabel_daftar_berita.html');
    }

    public function add_galeri($f3)
    {
        $this->checkAdmin($f3);

        if ($f3->get('VERB') === 'POST') {
              $judul = $f3->get('POST.judul');
              $deskripsi = $f3->get('POST.deskripsi');
              $kategori = $f3->get('POST.kategori');
              $tanggal = $f3->get('POST.tanggal');

              $file = $_FILES['gambar'];
              $filename = uniqid() . '_' . basename($file['name']);
              $target = 'uploads/gallery/' . $filename;

              if (move_uploaded_file($file['tmp_name'], $target)) {
                  $galeri = new \App\Models\GaleriModel($f3->get('DB'));
                  $galeri->insert([
                      'judul' => $judul,
                      'deskripsi' => $deskripsi,
                      'kategori' => $kategori,
                      'tanggal' => $tanggal,
                      'gambar' => $filename
                  ]);
                  $f3->reroute('/admin/gallery');
              } else {
                  $f3->set('error', 'Upload gagal. Pastikan file valid.');
              }
          }

        $this->init_admin_page($f3);
        $f3->set('active_menu_daftargaleri_f3', 'active');

        // Initialize variables for template
        $f3->set('item', ['judul' => '', 'deskripsi' => '', 'kategori' => '', 'tanggal' => date('Y-m-d')]);

        // Jika GET, tampilkan form
        $f3->set('template', 'gui-lieind/pages/admin/i_template_form_galeri.html');
        echo \Template::instance()->render('gui-lieind/pages/admin/Template_add-new-post-galeri.html');
        
      }
      
      public function edit_galeri($f3, $params) {
          
          $this->checkAdmin($f3);
          $id = $f3->get('PARAMS.id');
          $f3->set('id', $id);
        
          $galeri = new \App\Models\GaleriModel($f3->get('DB'));

          // Ambil data awal
          $item = $galeri->getById($id);
          if (!$item) {
              $f3->error(404, 'Data tidak ditemukan');
              return;
          }

          if ($f3->get('VERB') === 'POST') {
              $judul = $f3->get('POST.judul');
              $deskripsi = $f3->get('POST.deskripsi');
              $kategori = $f3->get('POST.kategori');
              $tanggal = $f3->get('POST.tanggal');

              $filename = $item['gambar']; // default: gambar lama

              if (!empty($_FILES['gambar']['name'])) {
                  $file = $_FILES['gambar'];
                  $filename = uniqid() . '_' . basename($file['name']);
                  $target = 'uploads/gallery/' . $filename;
                  move_uploaded_file($file['tmp_name'], $target);
              }

              $galeri->update($id, [
                  'judul' => $judul,
                  'deskripsi' => $deskripsi,
                  'kategori' => $kategori,
                  'tanggal' => $tanggal,
                  'gambar' => $filename
              ]);

              $f3->reroute('/admin/gallery');
          }
          
          $f3->set('item', $item);
          
          $this->init_admin_page($f3);
          $f3->set('active_menu_daftargaleri_f3', 'active');

          $f3->set('template', 'gui-lieind/pages/admin/i_template_form_galeri_edit.html');
          echo \Template::instance()->render('gui-lieind/pages/admin/Template_add-new-post-galeri.html');

      }
      
      
    public function hapus_galeri($f3)
    {
        $this->checkAdmin($f3);

        $id = $f3->get('POST.id');
        $model = new GaleriModel($f3->get('DB'));
        $model->delete($id);

        $f3->reroute('/admin/gallery');
    }




}