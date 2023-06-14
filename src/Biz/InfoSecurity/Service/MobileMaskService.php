<?php

namespace Biz\InfoSecurity\Service;

interface MobileMaskService
{
    public function maskMobile($mobile);

    public function encryptMobile($mobile);

    public function decryptMobile($encryptedMobile);
}
