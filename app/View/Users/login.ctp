<?php echo $this->Session->flash('auth'); ?>
<?php echo $this->Form->create('User'); ?>
<fieldset>
    <?php
    echo $this->Form->input('username');
    echo $this->Form->input('password');
    ?>
</fieldset>
<?php echo $this->Form->end('Login'); ?>