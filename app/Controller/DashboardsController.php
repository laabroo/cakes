<?php

class DashboardsController extends AppController {

    var $name = 'Dashboard';
    public $components = array('Session');
    public $helpers = array('Html', 'Form');

    public function index() {
        
    }

    function muser() {
        $this->redirect(array('controller' => 'users', 'action' => 'index'));
    }

    function mkoordinator() {
        $this->redirect(array('controller' => 'koordinators', 'action' => 'index'));
    }

    function mpasar() {
        $this->redirect(array('controller' => 'pasars', 'action' => 'index'));
    }

    function mbarang() {
        $this->redirect(array('controller' => 'barangs', 'action' => 'index'));
    }

}

?>
