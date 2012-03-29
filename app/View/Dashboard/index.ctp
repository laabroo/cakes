<div style="text-align: right;">
    <h1 class="title">Welcome <?php echo $current_user['username']; ?></h1>
</div>
<div class="entry">
    <ul>
        <li><?php echo $this->Html->link('User', array('controller' => 'users', 'action' => 'index')); ?></li>
        <li><?php echo $this->Html->link('Koordinator', array('controller' => 'koordinators', 'action' => 'index')); ?></li>
        <li><?php echo $this->Html->link('Pasar', array('controller' => 'pasars', 'action' => 'index')); ?></li>
        <li><?php echo $this->Html->link('Barang', array('controller' => 'barangs', 'action' => 'index')); ?></li>
        <li><?php echo $this->Html->link('Logout', array('controller' => 'users', 'action' => 'logout')); ?></li>
    </ul>
</div>