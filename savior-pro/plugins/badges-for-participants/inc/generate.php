<?php
if (!isset($_GET['secret_key']) || $_GET['secret_key'] !== 'msdbfsldf;l922lkn3lk4n4n' || !isset($_GET['data']) || empty($_GET['data'])) return;
define( 'WP_USE_THEMES', false );
define( 'SHORTINIT', true );
require_once ($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');
require __DIR__ . '/vendor/autoload.php';

use Da\QrCode\Format\vCardFormat;
use Da\QrCode\QrCode;


class vCardFormatExt extends \Da\QrCode\Format\AbstractFormat
{
    use \Da\QrCode\Traits\EmailTrait;
    use \Da\QrCode\Traits\UrlTrait;

    /**
     * @var string
     */
    public $fullName;
    /**
     * @var string
     */
    public $address;
    /**
     * @var string
     */
    public $workPhone;
    /**
     * @var string
     */
    public $organization;
    /**
     * @var string
     */
    public $note;

    /**
     * @return string
     */
    public function getText()
    {
        $data = [];
        $data[] = "BEGIN:VCARD";
        $data[] = "VERSION:4.0";
        $data[] = "FN:{$this->fullName}";

        if (!empty($this->email)) {
            $data[] = "EMAIL;TYPE=PREF,INTERNET:{$this->email}";
        }

        if (!empty($this->workPhone)) {
            $data[] = "TEL;TYPE=CELL:{$this->workPhone}";
        }

        if (!empty($this->note)) {
            $data[] = "NOTE:{$this->note}";
        }

        if (!empty($this->address)) {
            $data[] = "ADR;TYPE=intl:{$this->address}";
        }

        if (!empty($this->organization) && strlen(implode("\n", array_filter($data))) + strlen("ORG:{$this->organization}") < 230) {
            $data[] = "ORG:{$this->organization}";
        }

        $data[] = "END:VCARD";

        return implode("\n", array_filter($data));
    }
}


$data = $_GET['data'];
$data = base64_decode($data);
$data = json_decode($data, true);
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

if (isset($data['email']) && mb_strlen($data['email']) > 0) {
    $format->email = trim(sanitize_email($data['email']));
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
header('Content-Type: ' . $qrCode->getContentType());
$img = $qrCode->writeString();
echo $img;
