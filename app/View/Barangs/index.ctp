<h1>List Daftar Barang</h1>
<?php echo $this->Html->link('Tambah Barang', array('action' => 'add')); ?>
<table>
    <tr>
        <th>Id</th>
        <th>Kode Barang</th>
        <th>Nama Barang</th>
        <th>Harga</th>
        <th>Kode Pasar</th>
        <th>Action</th>
    </tr>
    <?php foreach ($barangs as $barang): ?>
        <tr>
            <td><?php echo $barang['Barang']['id']; ?></td>
            <td><?php echo $barang['Barang']['kode_barang']; ?></td>
            <td><?php echo $barang['Barang']['nama_barang']; ?></td>
            <td><?php echo $barang['Barang']['harga']; ?></td>
            <td><?php echo $barang['Barang']['pasar_id']; ?></td>
            <td><?php echo $this->Form->postLink('Delete', array('action' => 'delete', $barang['Barang']['id']), array('confirm' => 'Anda yakin?')); ?> | 
            <?php echo $this->Html->link('Edit', array('action' => 'edit', $barang['Barang']['id'])); ?></td>
        </tr>
    <?php endforeach; ?>
</table>

