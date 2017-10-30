<?php

namespace Tests;

use Codeages\Biz\Framework\Context\Biz;
use Codeages\Biz\Framework\Provider\RedisServiceProvider;
use Codeages\Biz\Framework\Provider\TokenServiceProvider;
use PHPUnit\Framework\TestCase;

class TokenServiceProviderTest extends TestCase
{
    public function testRegister_Default()
    {
        $biz = new Biz();
        $provider = new TokenServiceProvider();
        $provider->register($biz);

        $service = $biz->service('Token:TokenService');
        $this->assertInstanceOf('Codeages\Biz\Framework\Token\Service\Impl\DatabaseTokenServiceImpl', $service);
    }

    public function testRegister_Redis()
    {
        $biz = new Biz(array(
            'token_service.impl' => 'redis',
        ));
        $biz->register(new RedisServiceProvider());

        $provider = new TokenServiceProvider();
        $provider->register($biz);

        $this->assertEmpty($biz['migration.directories']);
        $service = $biz->service('Token:TokenService');
        $this->assertInstanceOf('Codeages\Biz\Framework\Token\Service\Impl\RedisTokenServiceImpl', $service);
    }
}
