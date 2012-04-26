<h2 class="title">List Daftar Kategory</h2>
<div class="entry">
<?php echo $this->Html->link('Tambah Kategori', array('action' => 'add')); ?>
<table>
    <tr>
        <th>Id</th>
        <th>Nama</th>
        <th>Action</th>
    </tr>
    <?php foreach ($categories as $category): ?>
        <tr>
            <td><?php echo $category['Category']['id']; ?></td>
            <td><?php echo $category['Category']['name']; ?></td>
            <td><?php echo $this->Form->postLink('Delete', array('action' => 'delete', $category['Category']['id']), array('confirm' => 'Anda yakin?')); ?> | 
            <?php echo $this->Html->link('Edit', array('action' => 'edit', $category['Category']['id'])); ?></td>
        </tr>
    <?php endforeach; ?>
</table>   
</div>
