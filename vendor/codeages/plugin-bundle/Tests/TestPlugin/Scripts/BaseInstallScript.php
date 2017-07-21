<?php

class BaseInstallScript
{
    protected $installMode = 'appstore';

    public function updateDatabase()
    {
    }

    public function initialize()
    {
    }

    public function setInstallMode($mode)
    {
        if (!in_array($mode, array('appstore', 'command'))) {
            throw new \RuntimeException("{$mode} is not validate install mode.");
        }

        $this->installMode = $mode;
    }
}
