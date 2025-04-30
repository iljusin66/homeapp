<?php
require_once 'Config/config.php';
require_once 'App/.php';
$oUser = new user();
?><!DOCTYPE html>
<html lang="cs">
    <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="<?= c_MainUrl; ?>Bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- https://icons.getbootstrap.com/ -->
    <link href="<?= c_MainUrl; ?>Bootstrap/css/icons/bootstrap-icons.css" rel="stylesheet">
    <title>Přihlášení do aplikace Home</title>
    <script src="<?= c_MainUrl; ?>inc/jquery-3.6.4.min.js"></script>
    <script src="<?= c_MainUrl; ?>Bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= c_MainUrl; ?>inc/clinterap.js?ch=<?= md5_file('inc/clinterap.js') ?>"></script>
    <style>
        .tile { background-color: #d2f4ea; border:white solid 0.15em; border-radius: 0.5em; padding:1em; }
        .tile:hover { background-color: #a6e9d5; }
        h2 { font-size:1.1em; min-height:3.6em; }
        p { min-height:3em; display: inline-block; }
    </style>
  </head>
    <body>
        <div class="row text-center mt-5">
        <form method="post" action="login.php" id="frmLogin" class="col-3 m-auto text-start">
            <div class="m-3">
                <label for="login" class="form-label">Přihlašovací jméno:</label>
                <input type="text" id="user" class="form-control" name="user" value="<?= $oUser->user ?>">
              </div>
              <div class="m-3">
                <label for="password" class="form-label">Heslo:</label>
                <input type="password" class="form-control" id="password" name="password">
                <div class="invalid-feedback mt-3" style="display:block;"> <?= implode(', ', (array)$oUser->aErr) ?> </div>
              </div>
              
              <button type="submit" class="btn btn-primary m-3">Přihlásit</button>
        </form>
        </div>
    </body>
</html>
