<?php
namespace App\Controllers;

use DB\SQL;

class SearchController {

	protected $db;

	public function __construct($f3) {
		$this->db = $f3->get('DB');
	}

	public function index($f3) {

		$q = trim($f3->get('GET.q'));

		if ($q == '') {
			$f3->reroute('/');
			return;
		}

		/* CONTOH SEARCH ARTIKEL */
		$sql = "
			SELECT id, judul, isi, tanggal_dibuat
			FROM artikel
			WHERE judul LIKE :q
			   OR isi LIKE :q
			ORDER BY tanggal_dibuat DESC
		";

		$result = $this->db->exec($sql, [
			':q' => '%' . $q . '%'
		]);

		$f3->set('keyword', $q);
		$f3->set('results', $result);

		echo \Template::instance()->render('gui-lieind/pages/search_result.html');
	}
}
