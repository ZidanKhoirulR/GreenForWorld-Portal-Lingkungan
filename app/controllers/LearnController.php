<?php
namespace App\Controllers;

class LearnController {
    function index($f3) {
        echo \Template::instance()->render('gui-lieind/pages/learn-more.html');
    }
}
