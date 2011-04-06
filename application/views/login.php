<?php
<h2>Login</h2>
<?php echo form_open('sessions/authenticate'); ?>
    <dl>
        <dt><?php echo form_label('Email', 'user_email'); ?></dt>
        <dd><?php echo form_input(array(
            'name' => 'user[email]', 
            'id' => 'user_email'
        )); ?></dd>

        <dt><?php echo form_label('Password', 'user_password'); ?></dt>
        <dd><?php echo form_password(array(
            'name' => 'user[password]', 
            'id' => 'user_password'
        )); ?></dd>
    </dl>
    <ul>
        <li><?php echo form_submit('submit', 'Login'); ?></li>
        <li><a href="<?php echo site_url('/'); ?>">Cancel</a></li>
    </ul>
<?php echo form_close(); ?>