<?php

namespace Biz\SCRM\Service;

interface SCRMService
{
    public function isSCRMBind();

    public function setUserSCRMData($user);

    public function setStaffSCRMData($user);

    public function getAssistantQrCode($assistant);

    public function getStaffBindQrCodeUrl($assistant);
}
