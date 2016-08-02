<form method="post" action="<?php echo url_for('register'); ?>">
    <div class="form-group <?php if (isset($errors['email'])) echo 'has-error' ?>">
        <input type="text" class="form-control" name="email" placeholder="E-mail"
               value="<?php if(isset($old['email'])) echo $old['email']; ?>">
        <?php if (isset($errors['email'])): ?>
            <span class="help-block"><?php echo $errors['email'][0] ?></span>
        <?php endif ?>
    </div>
    <div class="form-group <?php if (isset($errors['login'])) echo 'has-error' ?>">
        <input type="text" class="form-control" name="login" placeholder="Login"
               value="<?php if(isset($old['login'])) echo $old['login']; ?>">
        <?php if (isset($errors['login'])): ?>
            <span class="help-block"><?php echo $errors['login'][0] ?></span>
        <?php endif ?>
    </div>
    <div class="form-group <?php if (isset($errors['real_name'])) echo 'has-error' ?>">
        <input type="text" class="form-control" name="real_name" placeholder="Real Name"
               value="<?php if(isset($old['real_name'])) echo $old['real_name']; ?>">
        <?php if (isset($errors['real_name'])): ?>
            <span class="help-block"><?php echo $errors['real_name'][0] ?></span>
        <?php endif ?>
    </div>
    <div class="form-group <?php if (isset($errors['password'])) echo 'has-error' ?>">
        <!-- do not display old password for security reasons -->
        <input type="password" class="form-control" name="password" placeholder="Password">
        <?php if (isset($errors['password'])): ?>
            <span class="help-block"><?php echo $errors['password'][0] ?></span>
        <?php endif ?>
    </div>

    <div class="form-group <?php if (isset($errors['birth_date'])) echo 'has-error' ?>">
        Birth date
        <input type="date" class="form-control" name="birth_date" placeholder="1990-12-30"
               value="<?php if(isset($old['birth_date'])) echo $old['birth_date']; ?>">
        <?php if (isset($errors['birth_date'])): ?>
            <span class="help-block"><?php echo $errors['birth_date'][0] ?></span>
        <?php endif ?>
    </div>

    <div class="form-group <?php if (isset($errors['country_id'])) echo 'has-error' ?>">
        Your country
        <select name="country_id" class="form-control">
            <?php foreach ($countries as $country) : ?>
                <!-- TODO: old input -->
                <option value="<?php echo $country->id; ?>"><?php echo $country->name; ?></option>
            <?php endforeach ?>
        </select>
        <?php if (isset($errors['country_id'])): ?>
            <span class="help-block"><?php echo $errors['country_id'][0] ?></span>
        <?php endif ?>
    </div>

    <div class="form-group <?php if (isset($errors['agree_cond'])) echo 'has-error' ?>">
        <div class="checkbox">
            <label>
                <input type="checkbox" name="agree_cond" <?php if(isset($old['agree_cond'])) echo 'checked'; ?>>
                I agree with terms and conditions
            </label>
        </div>
        <?php if (isset($errors['agree_cond'])): ?>
            <span class="help-block"><?php echo $errors['agree_cond'][0] ?></span>
        <?php endif ?>
    </div>

    <button class="btn btn-primary" type="submit">Register</button>
</form>