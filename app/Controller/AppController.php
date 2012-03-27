<?php

class AppController extends Controller {

    public $components = array(
        'Session',
        'Auth' => array(
            'loginRedirect' => array('controller' => 'pasars', 'action' => 'index'),
            'logoutRedirect' => array('controller' => 'homes', 'action' => 'index', 'home')
        )
    );

    public function beforeFilter() {
        $this->Auth->allow('index', 'view');
    }

}

?>
