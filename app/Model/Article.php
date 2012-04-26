<?php

class Article extends AppModel {

    public $name = 'Article';
    public $validate = array(
        'title' => array(
            'rule' => 'notEmpty'
        ),
        'content' => array(
            'rule' => 'notEmpty'
        ),
        'categories_id' => array(
            'rule' => 'notEmpty'
        )
    );
    public $belongsTo = array
        (
        'Categories' => array
            (
            'className' => 'Category',
            'foregenKey' => 'categories_id'
        )
    );

}

?>
