<?php
function customError($errno, $errmessage, $errfile, $errline) {
  if (!(error_reporting() & $errno)) {
    // This error code is not included in error_reporting, so let it fall
    // through to the standard PHP error handler
    return false;
  }

  // Supported error types
  $php_error_types = [
    E_WARNING => 'E_WARNING',
    E_NOTICE => 'E_NOTICE',
    E_USER_ERROR => 'E_USER_ERROR',
    E_USER_WARNING => 'E_USER_WARNING',
    E_USER_NOTICE => 'E_USER_NOTICE',
    E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
    E_DEPRECATED => 'E_DEPRECATED',
    E_USER_DEPRECATED => 'E_USER_DEPRECATED',
    E_ALL => 'E_ALL'
  ];
  $error_content = '<div class="row '.$php_error_types["$errno"].'">';
  $error_content .= '<p class="message"><b>' . $php_error_types["$errno"] . '</b></p>' . $errmessage . '</pre>';
  $error_content .= '<p class="file">The error occurred on line <b>' . $errline . '</b> in file: </p><pre>' . $errfile . '</pre>';
  $error_content .= '<p class="systemInfo"><i>PHP ' . PHP_VERSION . ' (' . PHP_OS . ')</i></p>';
  $error_content .= '</div>';

  echo $error_content;

  /* Do not execute PHP internal error handler */
  return true;
}
//die('<pre>'.print_r($t::test));
set_error_handler('customError');

