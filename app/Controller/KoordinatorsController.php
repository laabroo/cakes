<?php

class KoordinatorsController extends AppController {

    var $tableName = 'koordinators';
    public $helpers = array('Form', 'Html');
    public $components = array('Session');

    public function index() {
        $this->set($this->tableName, $this->Koordinator->find('all'));
    }

    public function add() {
        if ($this->request->is('post')) {
            if ($this->Koordinator->save($this->request->data)) {
                $this->Session->setFlash('Koordinator berhasil di tambah.');
                $this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash('Koordinator gagal di tambah');
            }
        }
        $pasars = $this->Koordinator->Pasar->find('list', array('fields' => array('Pasar.id', 'Pasar.kode_pasar')));
        $this->set(compact('pasars'));
    }

    public function edit($id = null) {
        $this->Koordinator->id = $id;
        if ($this->request->is('get')) {
            $this->request->data = $this->Koordinator->read();
        } else {
            if ($this->Koordinator->save($this->request->data)) {
                $this->Session->setFlash('Koordinator berhasil diupdate.');
                $this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash('Koordinator gagal diupdate.');
            }
        }
        $pasars = $this->Koordinator->Pasar->find('list', array('fields' => array('Pasar.id', 'Pasar.kode_pasar')));
        $this->set(compact('pasars'));
    }

    public function delete($id) {
        if ($this->request->is('get')) {
            throw new MethodNotAllowedException();
        }
        if ($this->Koordinator->delete($id)) {
            $this->Session->setFlash('Koordinator berhasil dihapus.');
            $this->redirect(array('action' => 'index'));
        }
    }

}

?>
    