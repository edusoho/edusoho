<?php

namespace Codeages\Biz\Framework\Skeleton;

use Composer\Script\Event;

class ComposerScriptHandler
{
    public static function buildEnv(Event $event)
    {
        $io = $event->getIO();

        $io->write('<info>Creating the "env.php" file</info>');

        $exampleEnv = require 'env.php.example';

        $env = array();

        foreach ($exampleEnv as $key => $default) {
            $value = $io->ask(sprintf('<question>%s</question> (<comment>%s</comment>): ', $key, self::convertValueToDisplay($default)), $default);
            $env[$key] = self::convertValueToPhp($value);
        }

        $content = "<?php \n\nreturn ".var_export($env, true).';';

        \file_put_contents('env.php', $content);

        $io->write("\n");
        $io->write('<bg=green>                              </>');
        $io->write('<bg=green> [OK] "env.php" file created. </>');
        $io->write('<bg=green>                              </>');
        $io->write("\n");
    }

    protected static function convertValueToDisplay($value)
    {
        if (true === $value) {
            $displayValue = 'true';
        } elseif (false === $value) {
            $displayValue = 'false';
        } elseif ('' === $value) {
            $displayValue = '<<empty>>';
        } else {
            $displayValue = $value;
        }

        return $displayValue;
    }

    protected static function convertValueToPhp($value)
    {
        if ('true' === $value) {
            $phpValue = true;
        } elseif ('false' === $value) {
            $phpValue = false;
        } elseif (ctype_digit($value)) {
            $phpValue = intval($value);
        } else {
            $phpValue = $value;
        }

        return $phpValue;
    }
}
