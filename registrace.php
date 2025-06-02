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
    <title><?= c_AppName . ' / ' .__('Registrace do aplikace') ?></title>
    <script src="<?= c_MainUrl; ?>inc/jquery-3.6.4.min.js"></script>
    <script src="<?= c_MainUrl; ?>Bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= c_MainUrl; ?>inc/home.js?ch=<?= md5_file('inc/home.js') ?>"></script>
    <script src="<?= c_MainUrl; ?>inc/validation-form.js?ch=<?= md5_file('inc/validation-form.js') ?>"></script>
  </head>
    <body>
        <div class="row text-center mt-5">
          <div class="col-8 col-sm-3 m-auto text-start">
            <p class="lead"><?= __('Vytvořte si účet v aplikaci') . ' ' . c_AppName ?></p>
          </div>
          <div class="clearfix"></div>
          <form method="get" action="registrace.php" id="frmLogin" class="col-8 col-sm-3 m-auto text-start">
              <input type="hidden" name="action" value="registrace">
              <input type="hidden" name="valid" id="valid">
                <div class="m-3">
                  <label for="login" class="form-label"><?= __('Zadejte přihlašovací jméno') ?>:</label>
                  <input type="text" id="login" class="form-control" name="login" value="<?= $oUser->aUser["login"] ?>" data-required data-pattern="^[a-zA-Z0-9_\.-]{4,}$" title="<?= __('Přihlašovací jméno musí mít alespoň 4 znaky: běžná písmena nebo čísla') ?>">
                  <small class="form-text text-muted ps-2"><?= __('Minimálně 4 znaky') ?></small>
                  <div class="invalid-feedback mt-3 ps-2" style="display:block;"> <?= implode(', ', (array)$oUser->aErr['login']) ?> </div>
                </div>
                <div class="m-3">
                  <label for="password" class="form-label"><?= __('Zadejte heslo') ?>:</label>
                  <input type="password" class="form-control" id="password" name="password" data-required data-pattern="6" title="<?= __('Heslo musí mít alespoň 6 znaků') ?>">
                  <small class="form-text text-muted ps-2"><?= __('Minimálně 6 znaků') ?></small>
                  <div class="invalid-feedback mt-3 ps-2" style="display:block;"> <?= implode(', ', (array)$oUser->aErr['password']) ?> </div>
                </div>
                <div class="m-3">
                  <label for="password2" class="form-label"><?= __('Heslo pro kontrolu') ?>:</label>
                  <input type="password" class="form-control" id="password2" name="password2" data-required data-pattern="password2" title="<?= __('Hesla se musí shodovat') ?>">
                  <small class="form-text text-muted ps-2"><?= __('Zadejte heslo znovu') ?></small>
                  <div class="invalid-feedback mt-3 ps-2" style="display:block;"> <?= implode(', ', (array)$oUser->aErr['password2']) ?> </div>
                </div>
                <div class="m-3">
                  <label for="email" class="form-label"><?= __('E-mail') ?>:</label>
                  <input type="text" id="email" class="form-control" name="email" value="<?= htmlspecialchars($oUser->aUser["email"] ?? '') ?>" data-required data-pattern="email" title="<?= __('Zadejte platný e-mail') ?>">
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
