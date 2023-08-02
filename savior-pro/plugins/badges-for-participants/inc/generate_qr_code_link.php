<?php
if (!isset($_GET['l']) || empty($_GET['l'])) return;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/Encryption.php';
use Da\QrCode\QrCode;

if (filter_var($_GET['l'], FILTER_VALIDATE_URL)) {
    $link = $_GET['l'];

    $qrCode = new QrCode($link);

    header('Content-Type: ' . $qrCode->getContentType());

    echo $qrCode->writeString();
}