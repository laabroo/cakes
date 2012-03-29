<?php

class Barang extends AppModel {

    public $name = 'Barang';
    public $belongsTo = array(
        'Pasar' => array(
            'className' => 'Pasar',
            'foreignKey' => 'pasar_id',
            ));
    public $validate = array(
        'kode_barang' => array(
            'rule' => 'notEmpty'
        ),
        'nama_barang' => array(
            'rule' => 'notEmpty'
        ),
        'harga' => array(
            'rule' => 'notEmpty'
        ),
        'pasar_id' => array(
            'rule' => 'notEmpty'
        )
    );

}

?>
