<?php

namespace Codeages\Biz\Framework\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class TokenServiceProvider implements ServiceProviderInterface
{
    public function register(Container $biz)
    {
        $biz['token_service.impl'] = isset($biz['token_service.impl']) ? $biz['token_service.impl'] : 'database';
        $biz['token_service.gc_divisor'] = isset($biz['token_service.gc_divisor']) ? intval($biz['token_service.gc_divisor']) : 1000;

        $biz['@Token:TokenService'] = function ($biz) {
            $class = 'Codeages\\Biz\\Framework\\Token\\Service\\Impl\\'.ucfirst($biz['token_service.impl']).'TokenServiceImpl';

            return new $class($biz);
        };

        $biz['autoload.aliases']['Token'] = 'Codeages\\Biz\\Framework\\Token';

        $biz['console.commands'][] = function () use ($biz) {
            return new \Codeages\Biz\Framework\Token\Command\TableCommand($biz);
        };
    }
}
