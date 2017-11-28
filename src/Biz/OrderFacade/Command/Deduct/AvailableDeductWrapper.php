<?php

namespace Biz\OrderFacade\Command\Deduct;

use Biz\OrderFacade\Command\Command;
use Biz\OrderFacade\Product\Product;
use Codeages\Biz\Framework\Context\BizAware;

class AvailableDeductWrapper extends BizAware
{
    /**
     * @var Command[][]
     */
    private $commands;

    public function addCommand(Command $command, $priority = 1)
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

    public function wrapper(Product $product)
    {
        $commands = $this->commands;
        if (empty($commands)) {
            return;
        }

        foreach ($commands as $command) {
            $command['command']->execute($product);
        }
    }
}
