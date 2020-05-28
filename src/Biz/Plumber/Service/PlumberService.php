<?php

namespace Biz\Plumber\Service;

interface PlumberService
{
    const STATUS_EXECUTING = 'executing';

    const STATUS_STOPPED = 'stopped';

    public function canOperate();

    public function getPlumberStatus();

    public function start();

    public function restart();

    public function stop();
}
