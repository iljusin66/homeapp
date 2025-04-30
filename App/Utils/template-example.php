<?php
include_once 'cls.template.php';

$template = new template('template-example.html');
$template->Jmeno = 'Pepík Zdepa';
$template->NadpisStranky = 'Testovací stránka šablony';

$key = 0;
$template->aVars["ZBOZI"][$key]["Nazev"] = 'Svetr';
$template->aVars["ZBOZI"][$key]["Popis"] = 'Tenký';
$template->aVars["ZBOZI"][$key]["Key"] = $key+1;

$key = 1;
$template->aVars["ZBOZI"][$key]["Nazev"] = 'Boty';
$template->aVars["ZBOZI"][$key]["Popis"] = 'Vysoké <b>kožené</b>';
$template->aVars["ZBOZI"][$key]["Key"] = $key+1;

$key = 2;
$template->aVars["ZBOZI"][$key]["Nazev"] = 'Čepice';
$template->aVars["ZBOZI"][$key]["Popis"] = 'Chlupaté';
$template->aVars["ZBOZI"][$key]["Key"] = $key+1;

$key = 0;
$template->aVars["ZBOZI2"][$key]["Nazev"] = 'Šaty';
$template->aVars["ZBOZI2"][$key]["Popis"] = 'Barevné';
$template->aVars["ZBOZI2"][$key]["Key"] = $key+1;

$key = 1;
$template->aVars["ZBOZI2"][$key]["Nazev"] = 'Sukně';
$template->aVars["ZBOZI2"][$key]["Popis"] = 'Plisovaná';
$template->aVars["ZBOZI2"][$key]["Key"] = $key+1;

$template->render();
die($template->finalHTML);


