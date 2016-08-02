<?php if($current_user): ?>
<p>
    Hello, <?php echo $current_user->real_name; ?>
</p>
    Your email: <?php echo $current_user->email; ?>
</p>
<?php else: ?>
<p>
    Hello, Guest!
</p>
<?php endif; ?>