<?php

namespace Tests\Unit\Task\Job\Tools;

use Biz\Activity\Type\Text;

class MockedText extends Text
{
    public function sync($sourceActivity, $activity)
    {
        $this->sourceActivity = $sourceActivity;
        $this->activity = $activity;

        return array('id' => 2222222);
    }

    public function getSourceActivity()
    {
        return $this->sourceActivity;
    }

    public function getActivity()
    {
        return $this->activity;
    }
}
