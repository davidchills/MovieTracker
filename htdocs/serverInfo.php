<?php
session_start();
include_once($_SERVER["DOCUMENT_ROOT"].'/../php_classes/MOVIETRACKER.inc');
new AUTH();
print '<pre>';
print_r($_SESSION);
print '</pre>';
phpinfo();
?>