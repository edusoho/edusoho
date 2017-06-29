<?php

namespace Codeages\Biz\Framework\Dao;

use Phpmig\Console\PhpmigApplication;
use Symfony\Component\Console\Input\InputOption;

class MigrationApplication extends PhpmigApplication
{
    public function __construct($version = '1.0')
    {
        parent::__construct($version);
        $this->getDefinition()->addOption(new InputOption('--env', '-e', InputOption::VALUE_REQUIRED, 'The Environment name.', 'dev'));
    }
}
