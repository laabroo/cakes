<?php
echo $this->Form->create('User', array('action' => 'edit'));
echo $this->Form->input('username');
echo $this->Form->input('password', array('type' => 'password'));
echo $this->Form->input('level', array('options' => array('1' => 'Admin', '2' => 'User', '3' => 'Staff')));
echo $this->Form->end('Update Admin');
?>
