<?php

echo $this->Form->create('User');
echo $this->Form->input('nama');
echo $this->Form->input('username');
echo $this->Form->input('password', array('type' => 'password'));
echo $this->Form->input('level', array('options' => array('1' => 'Admin', '2' => 'User', '3' => 'Staff')));
echo $this->Form->end('Tambah Admin');
?>
