<?php

class Pasar extends AppModel {

    public $validate = array(
        'kode_pasar' => array(
            'rule' => 'notEmpty'
        ),
        'alamat' => array(
            'rule' => 'notEmpty'
        )
    );

}

?>
