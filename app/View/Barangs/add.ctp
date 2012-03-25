<h1>Tambah Barang Baru</h1>
<?php
echo $this->Form->create('Barang');
echo $this->Form->input('pasar_id', array('type' => 'select', 'options' => $pasars));
echo $this->Form->input('kode_barang');
echo $this->Form->input('nama_barang');
echo $this->Form->input('harga');
echo $this->Form->end('Tambah Barang');
?>
