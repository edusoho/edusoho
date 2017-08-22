<?php

namespace Biz\OrderFacade\Command;

use Biz\OrderFacade\Product\Product;
use Codeages\Biz\Framework\Context\BizAware;

class ProductMarketingWrapper extends BizAware
{
    /**
     * @var $commands Command[][]
     */
    private $commands;

    public function addCommand(Command $command, $priority)
    {
        $command->setBiz($this->biz);

        $this->commands[] = array(
            'command' => $command,
            'priority' => $priority,
        );

        uasort($this->commands, function ($a1, $a2) {
            return $a1['priority'] < $a2['priority'];
        });
    }

    public function run(Product $product)
    {
        $commands = $this->commands;
        if (empty($commands)) {
            return $product;
        }

        foreach ($commands as $command) {
            $command['command']->execute($product);
        }

        return $product;
    }
}
