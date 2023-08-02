<?php
if (!isset($_GET['name']) || empty($_GET['name']) || !isset($_GET['id']) || empty($_GET['id']) || !isset($_GET['type']) || empty($_GET['type'])) return;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/Encryption.php';

use Da\QrCode\QrCode;

if (filter_var($_GET['name'], FILTER_SANITIZE_STRING) && filter_var($_GET['id'], FILTER_SANITIZE_STRING) && filter_var($_GET['type'], FILTER_SANITIZE_STRING)) {
    $text = $_GET['name'];
    $id = $_GET['id'];
    $type = $_GET['type'];

    $type_title = 'Social Event';
    if ($type == 'session') {
        $type_title = 'Session';
    }

    $qrCode = new QrCode($type_title . ': ' . $id . '; Name: ' . $text);

    header('Content-Type: ' . $qrCode->getContentType());

    echo $qrCode->writeString();
}