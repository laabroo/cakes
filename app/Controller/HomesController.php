<?php

class HomesController extends AppController {

    public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->allow('login');
        
    }
    public function index() {
        
    }

    public function login() {
        
    }

    public function logout() {
        
    }

}

?>
