<?php

class Category extends AppModel {

    public $name = 'Categories';
    public $hasOne = 'Articles';
    public $validate = array(
        'name' => array(
            'rule' => 'notEmpty'
        )
    );

}
?>


