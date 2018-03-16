<?php

namespace Codeages\Biz\Invoice;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class InvoiceServiceProvider implements ServiceProviderInterface
{
    public function register(Container $biz)
    {
        $biz['migration.directories'][] = dirname(dirname(__DIR__)).'/migrations/invoice';
        $biz['autoload.aliases']['Invoice'] = 'Codeages\Biz\Invoice';

        $biz['console.commands'][] = function () use ($biz) {
            return new \Codeages\Biz\Invoice\Command\TableCommand($biz);
        };
    }
}