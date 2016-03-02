<?php
namespace Topxia\Service\Common;

class Mail
{
    private $mail;
    private $cloudMail;
    public function __construct($_mail, $_cloudMail)
    {
        $this->mail      = $_mail;
        $this->cloudMail = $_cloudMail;
    }

    public function getMail()
    {
        return $this->mail;
    }

    public function getCloudMail()
    {
        return $this->cloudMail;
    }
}
