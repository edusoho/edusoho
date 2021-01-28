<?php

namespace AppBundle\Component\MediaParser;

use AppBundle\Common\Exception\AbstractException;

class ParserException extends AbstractException
{
    const EXCEPTION_MODULE = 11;

    const PARSED_FAILED_LETV = 4041101;

    const PARSED_FAILED_NETEASE = 4041102;

    const PARSED_FAILED_QQ = 4041103;

    const PARSED_FAILED_YOUKU = 4041104;

    const PARSER_NOT_SUPPORT = 5001101;

    public $messages = [
        4041101 => 'exception.parsed.failed.letv',
        4041102 => 'exception.parsed.failed.netease',
        4041103 => 'exception.parsed.failed.qq',
        4041104 => 'exception.parsed.failed.youku',
        5001101 => 'exception.parser.not.support',
    ];
}
