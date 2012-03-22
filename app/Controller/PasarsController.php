<?php

class PasarsController extends AppController {

    var $table = "pasars";

    public function __construct($request = null, $response = null) {
        parent::__construct($request, $response);
    }

    public $helpers = array('Html', 'Form');

    public function index() {
        $this->set($this->table, $this->Pasar->find('all'));
    }

    public function add() {
        if ($this->request->is('post')) {
            if ($this->Pasar->save($this->request->data)) {
                $this->Session->setFlash("Pasar berhasil ditambah.");
                $this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash("Pasar gagal ditambah.");
            }
        }
    }

    public function edit($id = null) {
        $this->Pasar->id = $id;
        if ($this->request->is('get')) {
            $this->request->data = $this->Pasar->read();
        } else {
            if ($this->Pasar->save($this->request->data)) {
                $this->Session->setFlash("Pasar berhasil diupdate.");
                $this->redirect(array("action" => "index"));
            } else {
                $this->Session->setFlash("Pasar gagal diupdate.");
            }
        }
    }

    public function delete($id) {
        if ($this->request->is('get')) {
            throw new MethodNotAllowedException();
        }
        if ($this->Pasar->delete($id)) {
            $this->Session->setFlash("Pasar berhasil dihapus.");
            $this->redirect(array('action' => 'index'));
        }
    }

}

?>
