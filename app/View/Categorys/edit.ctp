<h2 class="title">Edit Kategory</h2>
<div class="entry">
<?php

echo $this->Form->create('Category', array('action' => 'edit'));
echo $this->Form->input('name');
echo $this->Form->end('Update');
?>
</div>