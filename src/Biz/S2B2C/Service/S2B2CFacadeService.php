<?php

namespace Biz\S2B2C\Service;

interface S2B2CFacadeService
{
    public function getMe();

    public function getSupplier();

    public function getSupplierPlatformApi();

    public function getS2B2CService();
}
