<?php

namespace Tests\Unit\Task\Job\Tools;

use Biz\Activity\Type\Text;

class MockedText extends Text
{
    public function sync($sourceActivity, $activity)
    {
        $this->sourceActivity = $sourceActivity;
        $this->syncActivity = $activity;

        return array('id' => 2222222);
    }

    public function copy($activity, $config = array())
    {
        $this->copiedActivity = $activity;

        return array();
    }

    public function getSourceActivity()
    {
        return $this->sourceActivity;
    }

    public function getSyncActivity()
    {
        return $this->syncActivity;
    }

    public function getCopiedActivity()
    {
        return $this->copiedActivity;
    }
}
