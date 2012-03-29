<h2 class="title">Edit Koordinator</h2>
<div class="entry">
    <?php
    echo $this->Form->create('Koordinator', array('action' => 'edit'));
    echo $this->Form->input('pasar_id', array('type' => 'select', 'options' => $pasars));
    echo $this->Form->input('kode_koordinator');
    echo $this->Form->input('nama');
    echo $this->Form->input('no_hp');
    echo $this->Form->input('id', array('type' => 'hidden'));
    echo $this->Form->end('Update Koordinator');
    ?>  
</div>


