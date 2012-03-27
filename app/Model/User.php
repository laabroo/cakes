<?php

App::uses('AuthComponent', 'Controller/Component');

class User extends AppModel {

    public $name = 'User';
    public $validate = array(
        'nama' => array(
            'required' => array(
                'rule' => array('notEmpty'),
                'message' => 'Nama harus di isi.'
            )
        )
        ,
        'username' => array(
            'required' => array(
                'rule' => array('notEmpty'),
                'message' => 'Username harus diisi.'
            )
        ),
        'password' => array(
            'required' => array(
                'rule' => array('notEmpty'),
                'message' => 'Password harus diisi.'
            )
        ),
        'level' => array(
            'valid' => array(
                'rule' => array('inList', array('admin', 'user', 'staff')),
                'message' => 'Pilih dengan benar',
                'allowEmpty' => false
            )
        )
    );

    public function beforeSave() {

        if (isset($this->data[$this->alias]['password'])) {
            $this->data[$this->alias]['password'] = AuthComponent::password($this->data[$this->alias['password']]);
        }
        return true;
    }

}

?>
