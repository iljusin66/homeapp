<?php
spl_autoload_register(function ($class) {
    $class = str_replace('\\', '/', $class);
    $class = str_replace('Latecka/', '', $class);
    if (strpos($class, 'Latecka') === 0) :
        die($class);
    endif;
    $class = str_replace('App/', '', $class);
    
    if (strpos($class, 'Config') === 0) :
        $file = __DIR__ . "/Config/config.php";
    else:
        $file = __DIR__ . "/App/" . $class . ".php";
    endif;
    if (file_exists($file)) :
        require_once $file;
    else:
        die("Chyba autoload: file $file not found.");
    endif;
});

require_once 'App/Utils/helper.php';