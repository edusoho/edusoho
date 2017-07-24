<?php
namespace Codeages\Weblib\Auth;

use PHPUnit\Framework\TestCase;
use Pimple\Container;

class TokenAlgoFactoryTest extends TestCase
{
    public function testFactory_Container_ValidAlgo()
    {
        $container = new Container();
        $container['weblib.auth.token_algo.signature'] = function () {
            return new SignatureTokenAlgo();
        };

        $factory = new TokenAlgoFactory($container);
        $algo = $factory->factory('signature');

        $this->assertInstanceOf('\Codeages\Weblib\Auth\SignatureTokenAlgo', $algo);
    }

    /**
     * @expectedException \Psr\Container\NotFoundExceptionInterface
     */
    public function testFactory_Container_InvalidAlgo()
    {
        $container = new Container();
        $container['weblib.auth.token_algo.signature'] = function () {
            return new SignatureTokenAlgo();
        };

        $factory = new TokenAlgoFactory($container);
        $algo = $factory->factory('none');
    }

    public function testFactory_NoContainer_ValidAlgo()
    {
        $factory = new TokenAlgoFactory();
        $algo = $factory->factory('signature');

        $this->assertInstanceOf('\Codeages\Weblib\Auth\SignatureTokenAlgo', $algo);
    }

    public function testFactory_NoContainer_InvalidAlgo()
    {
        $factory = new TokenAlgoFactory();
        $algo = $factory->factory('signature');

        $this->assertInstanceOf('\Codeages\Weblib\Auth\SignatureTokenAlgo', $algo);
    }
}