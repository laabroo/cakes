<h2 class="title">Tambah Artikel Baru</h2>
<div class="entry">
    <?php
    echo $this->Form->create('Article');
    echo $this->Form->input('title');
    echo $this->Form->input('content');
    echo $this->Form->input('status', array('options' => array('active' => 'Publish', 'draf' => 'Draf')));
    echo $this->Form->input('category_id', array('type' => 'select', 'options' => $category));
    echo $this->Form->end('Tambah Barang');
    ?>
</div>