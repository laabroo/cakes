<?php

class Pasar extends AppModel {

    public $name = 'Pasar';
    public $hasMany = array
        (
        'Koordinator' => array
            (
            'className' => 'Koordinator',
            'foregenKey' => 'pasar_id'
        )
    );
    public $validate = array(
        'kode_pasar' => array(
            'rule' => 'notEmpty'
        ),
        'nama_pasar' => array(
            'rule' => 'notEmpty'
        ),
        'alamat' => array(
            'rule' => 'notEmpty'
        )
    );
    public $hasOne = array(
        'Barang' => array(
            'className' => 'Barang',
            'foregenKey' => 'pasar_id'
        )
    );

}

?>
