<form method="post">
    <?php if ($form->isValid()): ?>
        <div class="alert alert-success">OK! Your form was submited :)</div>
    <?php endif; ?>


    <?php if (!$form->isValid('email')): ?>
        <div class="alert alert-error"><?= $form->getInputErrorMessage('email') ?></div>
    <?php endif; ?>
    <label>
        <span class="label">Email: </span>
        <input type="text" name="login[email]" value="<?= $form->email ?>" />
    </label>


    <?php if (!$form->isValid('password')): ?>
        <div class="alert alert-error"><?= $form->getInputErrorMessage('password') ?></div>
    <?php endif; ?>
    <label>
        <span class="label">Password: </span>
        <input type="password" name="login[pwd]" value="" />
    </label>
    <div class="action">
        <button type="submit">Submit me!</button>
    </div>
</form>

