<h1 class="title">Tambah User</h1>
<div class="entry">
<?php

echo $this->Form->create('User');
echo $this->Form->input('nama');
echo $this->Form->input('username');
echo $this->Form->input('email');
echo $this->Form->input('password', array('type' => 'password'));
echo $this->Form->input('password_confirmation', array('type' => 'password'));
echo $this->Form->input('level', array('options' => array('admin' => 'Admin', 'author' => 'Author', 'staff' => 'Staff')));
echo $this->Form->end('Tambah Admin');
?>
</div>