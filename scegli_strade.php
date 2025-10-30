<?php
require 'config_comuni.php';

header('Content-Type: application/json');

$comune = $_GET['comune'] ?? '';

if ($comune == 'RN') {
    require 'api_strade.php';
    $strade = getAllData(); // strade Rimini
} elseif ($comune == 'BE') {
    require 'api_strade_bellaria.php';
    $strade = getAllDataBellaria(); // strade Bellaria
} else {
    $strade = [];
}

echo json_encode($strade);
