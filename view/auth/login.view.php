<form method="post" action="<?php echo url_for('login'); ?>">
    <div class="form-group <?php if (isset($errors['login'])) echo 'has-error' ?>">
        <input type="text" class="form-control" name="login" placeholder="Login or email"
               value="<?php if(isset($old['login'])) echo $old['login']; ?>">
        <?php if (isset($errors['login'])): ?>
            <span class="help-block"><?php echo $errors['login'][0] ?></span>
        <?php endif ?>
    </div>

    <div class="form-group <?php if (isset($errors['password'])) echo 'has-error' ?>">
        <input type="password" class="form-control" name="password" placeholder="Password"
               value="">
        <?php if (isset($errors['password'])): ?>
            <span class="help-block"><?php echo $errors['password'][0] ?></span>
        <?php endif ?>
    </div>

    <button class="btn btn-primary" type="submit">Войти</button>
</form>