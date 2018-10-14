<?php
namespace Controllers;

class IndexController extends AbstractController {
    public function index()
    {
        require_once BASE_DIR . 'views/index.phtml';
    }
}