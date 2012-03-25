<h1>Edit Barang</h1>
<?php
echo $this->Form->create('Barang', array('action' => 'edit'));
echo $this->Form->input('pasar_id', array('type' => 'select', 'options' => $pasars));
echo $this->Form->input('kode_barang');
echo $this->Form->input('nama_barang');
echo $this->Form->input('harga');
echo $this->Form->input('id', array('type' => 'hidden'));
echo $this->Form->end('Update Barang');
?>
