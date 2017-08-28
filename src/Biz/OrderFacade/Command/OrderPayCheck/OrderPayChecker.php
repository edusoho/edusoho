<?php

namespace Biz\OrderFacade\Command\OrderPayCheck;

use Codeages\Biz\Framework\Context\BizAware;

class OrderPayChecker extends BizAware
{
    /**
     * @var OrderPayCheckCommand[][]
     */
    private $commands;

    public function addCommand(OrderPayCheckCommand $command, $priority = 1)
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

    public function check($order)
    {
        $commands = $this->commands;
        if (empty($commands)) {
            return;
        }

        foreach ($commands as $command) {
            $command['command']->execute($order);
        }
    }
}
