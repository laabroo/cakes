<h2 class="title">Tambah Koordinator Baru</h2>
<div class="entry">
<?php
echo $this->Form->create('Koordinator');
echo $this->Form->input('pasar_id',array('type'=>'select','options'=>$pasars));
echo $this->Form->input('kode_koordinator');
echo $this->Form->input('nama');
echo $this->Form->input('no_hp');
echo $this->Form->end('Tambah Koordinator');
?>  
</div>

