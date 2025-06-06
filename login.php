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
    <title><?= c_AppName . ' / ' . __('Přihlášení do aplikace') ?></title>
    <script src="<?= c_MainUrl; ?>inc/jquery-3.6.4.min.js"></script>
    <script src="<?= c_MainUrl; ?>Bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= c_MainUrl; ?>inc/home.js?ch=<?= md5_file('inc/home.js') ?>"></script>
  </head>
    <body>
        <div class="row text-center mt-5">
          <div class="col-8 col-sm-3 m-auto text-start">
            <p class="lead"><?= __('Přihlašte se do aplikace') . ' ' . c_AppName ?></p>
          </div>
          <div class="clearfix"></div>
        <form method="post" action="login.php" id="frmLogin" class="col-8 col-sm-3 m-auto text-start">
            <input type="hidden" name="action" value="login">
            <div class="m-3">
              <label for="email" class="form-label"><?= __('E-mail') ?>:</label>
              <input type="text" id="email" class="form-control" name="email" value="<?= $oUser->aUser["email"] ?>">
            </div>
            <div class="m-3">
              <label for="password" class="form-label"><?= __('Heslo') ?>:</label>
              <input type="password" class="form-control" id="password" name="password">
              <div class="invalid-feedback mt-3" style="display:block;"> <?= implode(', ', (array)$oUser->aErr) ?> </div>
            </div>
            
            <button type="submit" class="btn btn-primary m-3"><?= __('Přihlásit') ?></button>
        </form>
        <div class="mt-3">
          <a href="/registrace.php"><?= __('Nemáte ještě účet? Zaregistrujte se.') ?></a>
        </div>
        </div>
    </body>
</html>
