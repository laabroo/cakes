<h1 class="title">Daftar User</h1>
<div class="entry">
<?php echo $this->Html->link('Tambah User', array('action' => 'add')); ?>
<table>
    <tr>
        <th>Nama</th>
        <th>Username</th>
        <th>Level</th>
        <th>Action</th>
    </tr>
    <?php foreach ($users as $user): ?>
        <tr>
            <td><?php echo $user['User']['nama']; ?></td>
            <td><?php echo $user['User']['username']; ?></td>
            <td><?php echo $user['User']['level']; ?></td>
            <td>
                <?php echo $this->Form->postLink('Delete', array('action' => 'delete', $user['User']['id']), array('confirm' => 'Anda yakin?')); ?>
                <?php echo $this->Html->link('Edit', array('action' => 'edit', $user['User']['id'])); ?></td>
        </tr>
    <?php endforeach; ?>
</table>
</div>