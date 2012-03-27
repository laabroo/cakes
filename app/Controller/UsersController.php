<?php

class UsersController extends AppController {

    public $components = array('Session');
    public $helpers = array('Form', 'Html');
    var $table = 'users';

    public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->allow('add','logout');
    }

    public function index() {
//        $this->set($this->table, $this->User->find('all'));
        $this->User->recursive = 0;
        $this->set('users', $this->paginate());
    }

    public function view($id = null) {
        $this->User->id = $id;
        if (!$this->User->exists()) {
            throw new NotFoundException("Invalid user");
        }
        $this->set('user', $this->User->read(null, $id));
    }

    public function add() {
        if ($this->request->is('post')) {
            if ($this->User->save($this->request->data)) {
                $this->Session->setFlash('User Beharsil ditambahkan');
                $this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash('User Gagal ditambahkan.');
            }
        }
    }

    public function edit($id = null) {
        $this->User->id = $id;
        if (!$this->User->exists()) {
            throw new NotFoundException('Invalid User');
        }
        if ($this->request->is('post') || $this->request->is('put')) {
            if ($this->User->save($this->request->data)) {
                $this->Session->setFlash('User berhasil diupdate.');
                $this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash('Gagal mengupdate user.');
            }
        } else {
            $this->request->data = $this->User->read(null, $id);
        }
    }

    public function delete($id) {
        if ($this->request->is('post')) {
            throw new MethodNotAllowedException();
        }
        $this->User->id = $id;
        if ($this->User->exists()) {
            throw new NotFoundException('Invalid User.');
        }
        if ($this->User->delete()) {
            $this->Session->setFlash('User berhasil dihapus.');
            $this->redirect(array('action' => 'index'));
        } else {
            $this->Session->setFlash('User gagal dihapus.');
            $this->redirect(array('action' => 'index'));
        }
    }

    public function login() {
        if ($this->Auth->login()) {
            $this->redirect($this->Auth->redirect());
        } else {
            $this->Session->setFlash('Usernama dan password tidak valid.');
        }
    }

    public function logout() {
        $this->redirect($this->Auth->logout());
    }

}

?>
