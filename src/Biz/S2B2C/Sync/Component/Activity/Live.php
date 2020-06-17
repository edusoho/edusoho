<?php

namespace Biz\S2B2C\Sync\Component\Activity;

use Biz\Activity\Dao\LiveActivityDao;

class Live extends Activity
{
    public function sync($activity, $config = [])
    {
        $newLiveFields = $this->getLiveActivityFields($activity);

        return $this->getLiveActivityDao()->create($newLiveFields);
    }

    public function updateToLastedVersion($activity, $config = [])
    {
        $newLiveFields = $this->getLiveActivityFields($activity);

        $existLive = $this->getLiveActivityDao()->getBySyncId($newLiveFields['syncId']);
        if (!empty($existLive)) {
            return $this->getLiveActivityDao()->update($existLive[0]['id'], $newLiveFields);
        }

        return $this->getLiveActivityDao()->create($newLiveFields);
    }

    protected function getLiveActivityFields($activity)
    {
        $live = $activity[$activity['mediaType'].'Activity'];

        return [
            'syncId' => $live['id'],
            'liveId' => $live['liveId'],
            'progressStatus' => $live['progressStatus'],
            'liveProvider' => $live['liveProvider'],
            'roomType' => $live['roomType'],
            'roomCreated' => $live['roomCreated'],
        ];
    }

    /**
     * @return LiveActivityDao
     */
    protected function getLiveActivityDao()
    {
        return $this->getBiz()->dao('Activity:LiveActivityDao');
    }
}
