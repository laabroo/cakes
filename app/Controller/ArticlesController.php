<?php

class ArticlesController extends AppController {

    var $tableName = 'articles';
    public $helpers = array('Html', 'Form');
    public $components = array('Session');

    public function index() {
        $this->set($this->tableName, $this->Article->find('all'));
    }

    public function add() {
        if ($this->request->is('post')) {
            if ($this->Article->save($this->request->data)) {
                $this->Session->setFlash('Artikel berhasil dipublish.');
                $this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash('Artikel gagal dipublish.');
            }
        }
    }

    public function view($id = null) {
        $this->Article->id = $id;
        $this->set('article', $this->Article->read());
    }

    public function edit($id = null) {
        $this->Article->id = $id;
        if ($this->request->is('get')) {
            $this->request->data = $this->Article->read();
        } else {
            if ($this->Article->save($this->request->data)) {
                $this->Session->setFlash('Artikel berhasil diupdate.');
                $this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash('Artikel gagal diupdate.');
                $this->redirect(array('action' => 'index'));
            }
        }
    }

    public function delete($id) {
        $this->Article->id = $id;
        if ($this->Article->delete($id)) {
            $this->Session->setFlash('Artikel berhasil didelete.');
            $this->redirect(array('action' => 'index'));
        } else {
            $this->Session->setFlash('Artikel gagal dihapus.');
        }
    }

}

?>
