<?php

namespace Codeages\Biz\Framework\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Command\Command;

class EnvWriteCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('env:write')
            ->setDescription('Write environment variable to file.')
            ->addArgument('file', InputArgument::REQUIRED, 'The environment file.')
            ->addArgument('key', InputArgument::REQUIRED, 'The key of environment variable.')
            ->addArgument('value', InputArgument::REQUIRED, 'The value of environment variable.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = $input->getArgument('file');
        $key = $input->getArgument('key');
        $value = $input->getArgument('value');
        if ('true' == $value) {
            $value = true;
        } elseif ('false' == $value) {
            $value = false;
        }

        if (!file_exists($file)) {
            throw new \InvalidArgumentException('Environment file is not exist.');
        }

        $env = require $file;

        if (!is_array($env)) {
            throw new \RuntimeException('Environment file must return array.');
        }

        $env[$key] = $value;
        $env = var_export($env, true);

        file_put_contents($file, "<?php\nreturn ".$env.';');
    }
}
