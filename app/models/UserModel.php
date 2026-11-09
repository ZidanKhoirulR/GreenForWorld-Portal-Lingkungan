<?php

namespace App\Models;

class UserModel {
    protected $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getByLogin($login) {
        return $this->db->exec("SELECT * FROM tb_arsipuser WHERE login = ? AND status = 1", [$login])[0] ?? null;
    }
}