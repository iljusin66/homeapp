<?php
require_once 'utils.php';

use Latecka\HomeApp\Utils\utils;
use Latecka\HomeApp\Utils\l;

new utils();

/**
 * Globalne pristupna funkce, pro preklad
 * @param string $input Textový řetězec pro překlad
 * @param type $sekce Sekce webu ve ktere se preklad objevuje
 * @param type $forcedLang Vynucení konkrétní jazykové verze, nezávislé na aktuálně zobrazené jazykové verze webu
 * @return string
 */
function __(string $input = "", string $sekce = "", string $forcedLang = '') {
    global $o_Translator;
    if (empty($input)) { return ''; }
    if (!is_object($o_Translator)) { $o_Translator = new l(); }
    return $o_Translator->t($input, $sekce, $forcedLang);
}

if (!function_exists('debug')) {
    function debug($val) {
        utils::debug($val);
    }
}