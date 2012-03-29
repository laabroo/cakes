<?php

class AppController extends Controller {

    public $components = array(
        'Session',
        'Auth' => array(
            'loginRedirect' => array('controller' => 'dashboards', 'action' => 'index'),
            'logoutRedirect' => array('controller' => 'homes', 'action' => 'index'),
            'authError' => "Silahkan login terlebih dahulu.",
            'authorize' => array('Controller')
        )
    );

    public function isAuthorized($user) {
        return true;
    }

    public function beforeFilter() {
        $this->Auth->allow('view');
        $this->set('logged_in', $this->Auth->loggedIn());
        $this->set('current_user', $this->Auth->user());
    }

}

?>
