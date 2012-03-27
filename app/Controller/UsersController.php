<?php

class UsersController extends AppController {

    public $components = array('Session');
    public $helpers = array('Form', 'Html');
    var $table = 'users';

    public function index() {
        $this->set($this->table, $this->User->find('all'));
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
        if ($this->request->is('get')) {
            $this->request->data = $this->User->read();
        } else {
            if ($this->User->save($this->request->data)) {
                $this->Session->setFlash('User berhasil di update.');
                $this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash('User gagal diupdate.');
            }
        }
    }

    public function delete($id) {
        if ($this->request->is('get')) {
            throw new MethodNotAllowedException();
        }
        if ($this->User->delete($id)) {
            $this->Session->setFlash('User berhasil dihapus.');
            $this->redirect(array('action' => 'index'));
        }
    }

}

?>
