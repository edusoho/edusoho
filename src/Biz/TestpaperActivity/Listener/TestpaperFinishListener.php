<?php

namespace Biz\TestpaperActivity\Listener;

use Biz\Activity\Listener\Listener;

class TestpaperFinishListener extends Listener
{
    public function handle($activity, $data)
    {
        $event = $data['event'];

    }

}
