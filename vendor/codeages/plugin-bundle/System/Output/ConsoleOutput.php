<?php

namespace Codeages\PluginBundle\System\Output;

use Symfony\Component\Console\Output\OutputInterface as ConsoleOutputInterface;

class ConsoleOutput implements OutputInterface
{
    protected $output;

    public function __construct(ConsoleOutputInterface $output)
    {
        $this->output = $output;
    }

    public function write($message)
    {
        $this->output->write($message);
    }

    public function writeln($message)
    {
        $this->output->writeln($message);
    }
}
