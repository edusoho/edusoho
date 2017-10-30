<?php

namespace Topxia\MobileBundleV2\Processor;

interface OrderProcessor
{
    public function validateIAPReceipt();

    public function getPaymentMode();

    public function getPayOrder();

    public function createOrder();
}
