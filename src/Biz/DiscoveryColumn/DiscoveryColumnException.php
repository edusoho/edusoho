<?php

namespace Biz\DiscoveryColumn;

use AppBundle\Common\Exception\AbstractException;

class DiscoveryColumnException extends AbstractException
{
    const EXCEPTION_MODULE = 65;

    const NOTFOUND_DISCOVERY_COLUMN = 4046501;

    public $messages = [
        4046501 => 'exception.discovery_column.not_found',
    ];
}
