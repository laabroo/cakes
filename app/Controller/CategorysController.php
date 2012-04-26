<?php

class CategorysController extends AppController {

    var $table = "categories";
    public $helpers = array('Html', 'Form');
    public $components = array('Session');

    public function index() {
        $this->Category->reqursive = 0;
        $this->set('categories', $this->paginate());
    }

    public function add() {
        if ($this->request->is('post')) {
            if ($this->Category->save($this->request->data)) {
                $this->Session->setFlash("Category berhasil ditambah.");
                $this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash("Category gagal ditambah.");
            }
        }
    }

    public function edit($id = null) {
        $this->Category->id = $id;
        if ($this->request->is('get')) {
            $this->request->data = $this->Category->read();
        } else {
            if ($this->Category->save($this->request->data)) {
                $this->Session->setFlash("Category berhasil diupdate.");
                $this->redirect(array("action" => "index"));
            } else {
                $this->Session->setFlash("Category gagal diupdate.");
            }
        }
    }

    public function delete($id) {
        if ($this->request->is('get')) {
            throw new MethodNotAllowedException();
        }
        if ($this->Category->delete($id)) {
            $this->Session->setFlash("Category berhasil dihapus.");
            $this->redirect(array('action' => 'index'));
        }
    }

}

?>
