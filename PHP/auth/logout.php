<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Pharmacie_S/vendor/autoload.php'; // Autoloading de Composer

session_start();
session_unset();
session_destroy();

header('Location: /Pharmacie_S/index.php');
exit();
?>