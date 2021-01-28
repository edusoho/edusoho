<?php

namespace Biz\Common;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Codeages\Biz\Framework\Util\ReadableJsonFormatter;

class JsonLogger extends Logger
{
    public function __construct($name, StreamHandler $stream, array $handlers = array(), array $processors = array())
    {
        parent::__construct($name, $handlers, $processors);
        $formatter = new ReadableJsonFormatter();
        $stream->setFormatter($formatter);
        $this->pushHandler($stream);
        $this->pushTraceProcessor();
    }

    protected function pushTraceProcessor()
    {
        if (isset($_SERVER['TRACE_ID']) && $_SERVER['TRACE_ID']) {
            $this->pushProcessor(function ($record) {
                $record['extra']['trace_id'] = $_SERVER['TRACE_ID'];

                return $record;
            });
        }
    }
}
