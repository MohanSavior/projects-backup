<?php

require __DIR__ . '/vendor/autoload.php';

use Da\QrCode\Format\vCardFormat;
use Da\QrCode\QrCode;
use Da\QrCode\Response\QrCodeResponse;

require __DIR__ . '/vCardFormatExt.php';

class GenerateQRCode
{

    public static function render($data = array())
    {
        if (empty($data)) {
            return false;
        }

        $format = new vCardFormatExt();
        $format->fullName = $data['first_name'] . ' ' . $data['last_name'];

        if (isset($data['visitor_type']) && mb_strlen($data['visitor_type']) > 0 && $data['visitor_type'] == "Day Pass") {
            $format->note = "Day Pass";
        }

        if (isset($data['company']) && mb_strlen($data['company']) > 0) {
            $format->organization = $data['company'];
        }

        if (isset($data['phone_daytime']) && mb_strlen($data['phone_daytime']) > 0) {
            $format->workPhone = $data['phone_daytime'];
        }

        if (isset($data['email']) && mb_strlen($data['email']) > 0 && filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $format->email = trim(filter_var($data['email'], FILTER_VALIDATE_EMAIL));
        }

        $address = [];
        if (isset($data['addr_addr_2']) && mb_strlen($data['addr_addr_2']) > 0) {
            $address[] = $data['addr_addr_2'];
        }
        if (isset($data['addr_addr_1']) && mb_strlen($data['addr_addr_1']) > 0) {
            $address[] = $data['addr_addr_1'];
        }
        if (isset($data['addr_city']) && mb_strlen($data['addr_city']) > 0) {
            $address[] = $data['addr_city'];
        }
        if (isset($data['addr_state']) && mb_strlen($data['addr_state']) > 0) {
            $address[] = $data['addr_state'];
        }
        if (isset($data['addr_country']) && mb_strlen($data['addr_country']) > 0) {
            $address[] = $data['addr_country'];
        }
        if (isset($data['addr_zip']) && mb_strlen($data['addr_zip']) > 0) {
            $address[] = $data['addr_zip'];
        }
        $address = implode(', ', $address);

        if (mb_strlen($address) > 0) {
            $format->address = $address;
        }

        $qrCode = (new QrCode($format))->setSize(224)->setMargin(0);

        return $qrCode->writeDataUri();
    }
}