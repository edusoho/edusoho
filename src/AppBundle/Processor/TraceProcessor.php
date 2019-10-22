<?php

namespace AppBundle\Processor;

class TraceProcessor
{
    public function __construct()
    {
    }

    public function __invoke(array $record)
    {
        if (isset($_SERVER['TRACE_ID']) && $_SERVER['TRACE_ID']) {
            $record['extra']['trace_id'] = $_SERVER['TRACE_ID'];
        }

        return $record;
    }
}
