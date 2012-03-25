<?php

class BarangsController extends AppController {

    var $table = 'barangs';
    var $helpers = array('Form', 'Html');
    var $components = array('Session');

    public function index() {
        return $this->set($this->table, $this->Barang->find('all'));
    }

    public function add() {
        if ($this->request->is('post')) {
            if ($this->Barang->save($this->request->data)) {
                $this->Session->setFlash('Barang berhasil di tambahkan.');
                $this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash('Barang gagal ditambahkan.');
            }
        }
        $pasars = $this->Barang->Pasar->find('list', array('fields' => array('Pasar.id', 'Pasar.nama_pasar')));
        $this->set(compact('pasars'));
    }

    public function edit($id = null) {
        $this->Barang->id = $id;
        if ($this->request->is('get')) {
            $this->request->data = $this->Barang->read();
        } else {
            if ($this->Barang->save($this->request->data)) {
                $this->Session->setFlash('Barang berhasil diupdate.');
                $this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash('Barang gagal diupdate.');
            }
        }

        $pasars = $this->Barang->Pasar->find('list', array('fields' => array('Pasar.id', 'Pasar.nama_pasar')));
        $this->set(compact('pasars'));
    }

    public function delete($id) {
        if ($this->request->is(get)) {
            throw new MethodNotAllowedException();
        }
        if ($this->Barang->delete($id)) {
            $this->Session->setFlash('Barang berhasil didelete.');
            $this->redirect(array('action' => 'index'));
        }
    }

}

?>
