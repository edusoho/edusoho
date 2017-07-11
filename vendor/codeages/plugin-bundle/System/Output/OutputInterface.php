<?php

namespace Codeages\PluginBundle\System\Output;

interface OutputInterface
{
    public function write($text);

    public function writeln($text);
}
