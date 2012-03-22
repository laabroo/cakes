<h1>Edit Pasar</h1>
<?php
echo $this->Form->create('Pasar', array('action' => 'edit'));
echo $this->Form->input('kode_pasar');
echo $this->Form->input('nama_pasar');
echo $this->Form->input('alamat');
echo $this->Form->input('id', array('type' => 'hidden'));
echo $this->Form->end('Update Pasar');
?>
