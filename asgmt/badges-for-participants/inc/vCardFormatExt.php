<?php

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