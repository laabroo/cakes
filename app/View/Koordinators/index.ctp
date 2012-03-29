<h2 class="title"><a href="#">List Koordinator Koordinator</a></h2>
<div class="entry">
    <?php echo $this->Html->link('Tambah Koordinator', array('action' => 'add')); ?>
    <table>
        <tr>
            <th>id</th>
            <th>Nama Pasar</th>
            <th>Kode Koordinator</th>
            <th>Nama</th>
            <th>No HP</th>
            <th>Action</th>
        </tr>
        <?php foreach ($koordinators as $koordinator) : ?>
            <tr>
                <td><?php echo $koordinator['Koordinator']['id']; ?></td>
                <td><?php echo $koordinator['Koordinator']['pasar_id']; ?></td>
                <td><?php echo $koordinator['Koordinator']['kode_koordinator']; ?></td>
                <td><?php echo $koordinator['Koordinator']['nama']; ?></td>
                <td><?php echo $koordinator['Koordinator']['no_hp']; ?></td>
                <td><?php
        echo $this->Form->postLink(
                'Delete', array('action' => 'delete', $koordinator['Koordinator']['id']), array('confirm' => 'Anda yakin?')
        );
            ?></td>
                <td><?php echo $this->Html->link('Edit', array('action' => 'edit', $koordinator['Koordinator']['id'])); ?> </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
