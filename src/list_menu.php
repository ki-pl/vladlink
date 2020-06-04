<?php
require_once('../vendor/autoload.php');
use Vladlink\Menu;

$menu = new Menu();
$json = file_get_contents('categories.json');
$menu->loadMenu($json); // Операция по загрузке
$menu->generateType_A();
$menu->generateType_B();
$menu->print();
?>
