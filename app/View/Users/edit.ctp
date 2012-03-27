<?php

echo $this->Form->create('User', array('action' => 'edit'));
echo $this->Form->input('nama');
echo $this->Form->input('username');
echo $this->Form->input('password', array('type' => 'password'));
echo $this->Form->input('level', array('options' => array('admin' => 'Admin', 'author' => 'Author', 'staff' => 'Staff')));
echo $this->Form->end('Update User');
?>
