<?php

namespace AgentBundle\Executor;

class CommandExecutor
{
    public function execute(string $instruction, array $data): array
    {
        $parsed = InstructionParser::parse($instruction);
        $service = ServiceRegistry::resolve($parsed['service']);

        return $service->{$parsed['method']}($data);
    }
}
