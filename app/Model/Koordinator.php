<?php

class Koordinator extends AppModel {

    public $name = 'Koordinator';
    public $belongsTo = array(
        'Pasar' => array(
            'className' => 'Pasar',
            'foreignKey' => 'pasar_id',
            ));
    public $validate = array(
        'pasar_id' => array(
            'rule' => 'notEmpty'
        ),
        'kode_koordinator' => array(
            'rule' => 'notEmpty'
        ),
        'nama' => array(
            'rule' => 'notEmpty'
        ),
        'no_hp' => array(
            'rule' => 'alphaNumeric'
        )
    );

}

?>
