<form method="post" action="<?php echo url_for('login'); ?>">
    <div class="form-group <?php if (isset($errors['email'])) echo 'has-error' ?>">
        <input type="text" class="form-control" name="email" placeholder="Login or email"
               value="<?php if(isset($old['email'])) echo $old['email']; ?>">
        <?php if (isset($errors['email'])): ?>
            <span class="help-block"><?php echo $errors['email'][0] ?></span>
        <?php endif ?>
    </div>

    <div class="form-group <?php if (isset($errors['password'])) echo 'has-error' ?>">
        <input type="password" class="form-control" name="password" placeholder="Password"
               value="">
        <?php if (isset($errors['password'])): ?>
            <span class="help-block"><?php echo $errors['password'][0] ?></span>
        <?php endif ?>
    </div>

    <button class="btn btn-default" type="submit">Войти</button>
</form>