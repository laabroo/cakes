<h2 class="title">Tambah Barang Baru</h2>
<div class="entry">
    <?php
echo $this->Form->create('Barang');
echo $this->Form->input('pasar_id', array('type' => 'select', 'options' => $pasars));
echo $this->Form->input('kode_barang');
echo $this->Form->input('nama_barang');
echo $this->Form->input('harga');
echo $this->Form->end('Tambah Barang');
?>
</div>


