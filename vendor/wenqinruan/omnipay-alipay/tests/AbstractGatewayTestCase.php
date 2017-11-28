<?php

namespace Omnipay\Alipay\Tests;

use Omnipay\Tests\GatewayTestCase;

abstract class AbstractGatewayTestCase extends GatewayTestCase
{

    protected $partner = ALIPAY_PARTNER;

    protected $key = ALIPAY_KEY;

    protected $sellerId = ALIPAY_SELLER_ID;

    protected $appId = ALIPAY_APP_ID;

    protected $appPrivateKey = ALIPAY_APP_PRIVATE_KEY;

    protected $appEncryptKey = ALIPAY_APP_ENCRYPT_KEY;


    protected function setUp()
    {
        parent::setUp();
    }
}
