<h1>Tambah Pasar Baru</h1>

<?php
echo $this->Form->create('Pasar');
echo $this->Form->input('kode_pasar');
echo $this->Form->input('nama_pasar');
echo $this->Form->input('alamat');
echo $this->Form->end('Tambah Pasar');
?>
