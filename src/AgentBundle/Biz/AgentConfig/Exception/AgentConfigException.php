<?php

namespace AgentBundle\Biz\AgentConfig\Exception;

use AppBundle\Common\Exception\AbstractException;

class AgentConfigException extends AbstractException
{
    const EXCEPTION_MODULE = 91;

    const AGENT_CONFIG_ALREADY_CREATED = 4009101;

    const UNKNOWN_DOMAIN = 4009102;

    public $messages = [
        4009101 => '',
        4009102 => '',
    ];
}
