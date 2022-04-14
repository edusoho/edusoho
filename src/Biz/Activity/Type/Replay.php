<?php

namespace Biz\Activity\Type;

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Config\Activity;
use Biz\Activity\Dao\ReplayActivityDao;
use Biz\Activity\Service\ActivityService;

class Replay extends Activity
{
    protected function registerListeners()
    {
        return [];
    }

    public function get($targetId)
    {
        return $this->getReplayActivityDao()->get($targetId);
    }

    public function find($ids, $showCloud = 1)
    {
        return $this->getReplayActivityDao()->findByIds($ids);
    }

    public function copy($activity, $config = [])
    {
        $replay = $this->getReplayActivityDao()->get($activity['mediaId']);
        $newReplay = [
            'finish_type' => $replay['finish_type'],
            'finish_detail' => $replay['finish_detail'],
            'origin_lesson_id' => $replay['origin_lesson_id'],
        ];

        return $this->getReplayActivityDao()->create($newReplay);
    }

    public function sync($sourceActivity, $activity)
    {
        $sourceReplay = $this->getReplayActivityDao()->get($sourceActivity['mediaId']);
        $replay = $this->getReplayActivityDao()->get($activity['mediaId']);
        $replay['finish_type'] = $sourceReplay['finish_type'];
        $replay['finish_detail'] = $sourceReplay['finish_detail'];
        $replay['origin_lesson_id'] = $sourceReplay['origin_lesson_id'];

        return $this->getReplayActivityDao()->update($replay['id'], $replay);
    }

    public function update($targetId, &$fields, $activity)
    {
        $this->fieldsTrans($fields);
        $replay = ArrayToolkit::parts(
            $fields,
            [
                'finish_type',
                'finish_detail',
                'origin_lesson_id',
            ]
        );

        return $this->getReplayActivityDao()->update($targetId, $replay);
    }

    public function delete($targetId)
    {
        return $this->getReplayActivityDao()->delete($targetId);
    }

    public function create($fields)
    {
        $this->fieldsTrans($fields);
        $replay = ArrayToolkit::parts(
            $fields,
            [
                'finish_type',
                'finish_detail',
                'origin_lesson_id',
            ]
        );

        return $this->getReplayActivityDao()->create($replay);
    }

    protected function fieldsTrans(&$fields)
    {
        if (isset($fields['finishType'])) {
            $fields['finish_type'] = $fields['finishType'];
        }

        return $fields;
    }

    /**
     * @return ReplayActivityDao
     */
    protected function getReplayActivityDao()
    {
        return $this->getBiz()->dao('Activity:ReplayActivityDao');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->getBiz()->service('Activity:ActivityService');
    }
}
