<h2 class="title">Tambah Kategory</h2>
<div class="entry">
<?php

echo $this->Form->create('Category');
echo $this->Form->input('name');
echo $this->Form->end('Tambah');

?>
</div>