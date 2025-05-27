<?php
require_once 'autoload.php';

$oUser = new user();
?><!DOCTYPE html>
<html lang="cs">
    <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="<?= c_MainUrl; ?>Bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- https://icons.getbootstrap.com/ -->
    <link href="<?= c_MainUrl; ?>Bootstrap/css/icons/bootstrap-icons.css" rel="stylesheet">
    <title><?= __('Registrace do aplikace Home') ?></title>
    <script src="<?= c_MainUrl; ?>inc/jquery-3.6.4.min.js"></script>
    <script src="<?= c_MainUrl; ?>Bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= c_MainUrl; ?>inc/home.js?ch=<?= md5_file('inc/home.js') ?>"></script>
  </head>
    <body>
        <div class="row text-center mt-5">
          <form method="post" action="registrace.php" id="frmLogin" class="col-8 col-sm-3 m-auto text-start">
              <input type="hidden" name="action" value="registrace">
                <div class="m-3">
                  <label for="login" class="form-label"><?= __('Zadejte přihlašovací jméno') ?>:</label>
                  <input type="text" id="login" class="form-control" name="login" value="<?= $oUser->aUser["login"] ?>" required pattern="^[a-zA-Z0-9]{4,}$">
                  <small class="form-text text-muted ps-2"><?= __('Minimálně 4 znaky') ?></small>
                  <div class="invalid-feedback mt-3 ps-2" style="display:block;"> <?= implode(', ', (array)$oUser->aErr['login']) ?> </div>
                </div>
                <div class="m-3">
                  <label for="password" class="form-label"><?= __('Zadejte heslo') ?>:</label>
                  <input type="password" class="form-control" id="password" name="password" required pattern="^[a-zA-Z0-9]{6,}$" title="<?= __('Heslo musí mít alespoň 6 alfanumerických znaků') ?>">
                  <small class="form-text text-muted ps-2"><?= __('Minimálně 6 znaků') ?></small>
                  <div class="invalid-feedback mt-3 ps-2" style="display:block;"> <?= implode(', ', (array)$oUser->aErr['heslo']) ?> </div>
                </div>
                <div class="m-3">
                  <label for="email" class="form-label"><?= __('E-mail') ?>:</label>
                  <input type="email" id="email" class="form-control" name="email" value="<?= htmlspecialchars($oUser->aUser["email"] ?? '') ?>" required>
                  <small class="form-text text-muted ps-2"><?= __('Zadejte platný e-mail') ?></small>
                  <div class="invalid-feedback mt-3 ps-2" style="display:block;"> <?= implode(', ', (array)$oUser->aErr['email']) ?> </div>
                </div>
                
                <button type="submit" class="btn btn-primary m-3"><?= __('Zaregistrovat') ?></button>
          </form>
          <div class="mt-3">
            <a href="/login.php"><?= __('Máte už účet? Přihlašte se.') ?></a>
          </div>
        </div>
    </body>
</html>
