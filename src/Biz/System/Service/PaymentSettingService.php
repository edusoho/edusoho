<?php

namespace Biz\System\Service;

interface PaymentSettingService
{
    public function get($default = []);

    public function set($value);
}
