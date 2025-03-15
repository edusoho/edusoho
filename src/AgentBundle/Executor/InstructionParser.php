<?php

namespace AgentBundle\Executor;

use AgentBundle\Common\ResponseBuilder;

class InstructionParser
{
    public static function parse(string $instruction): array
    {
        // 支持两种指令格式：service.action 或 service/action
        $parts = preg_split('/[.\/]/', $instruction);
        if (2 !== count($parts)) {
            return ResponseBuilder::error('400001', '', 400);
        }

        return [
            'service' => ucfirst($parts[0]).'Service',
            'method' => $parts[1],
        ];
    }
}
