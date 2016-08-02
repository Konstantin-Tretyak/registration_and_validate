<div>
    <?php echo $user->email; ?>
</div>
<div>
    <?php echo $user->real_name; ?>
</div>
<a href="<?php echo url(url_for('logout')); ?>">
    Выйти
</a>
