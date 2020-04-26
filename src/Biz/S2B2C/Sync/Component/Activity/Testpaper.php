<?php

namespace Biz\S2B2C\Sync\Component\Activity;

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Dao\TestpaperActivityDao;
use Biz\Activity\Service\TestpaperActivityService;

class Testpaper extends Activity
{
    public function sync($activity, $config = array())
    {
        if ('testpaper' !== $activity['mediaType']) {
            return null;
        }

        $testpaperActivity = $activity[$activity['mediaType'].'Activity'];
        if (empty($testpaperActivity)) {
            return array();
        }

        $newExt = $this->getTestpaperActivityFields($testpaperActivity, $config);

        return $this->create($newExt);
    }

    public function updateToLastedVersion($activity, $config = array())
    {
        if ('testpaper' !== $activity['mediaType']) {
            return null;
        }

        $testpaperActivity = $activity[$activity['mediaType'].'Activity'];
        if (empty($testpaperActivity)) {
            return array();
        }
        $newExt = $this->getTestpaperActivityFields($testpaperActivity, $config);
        $newTestpaperFields = $this->filterFields($newExt);

        $existTestpaper = $this->getTestpaperActivityDao()->search(array('syncId' => $newTestpaperFields['syncId']), array(), 0, PHP_INT_MAX);
        if (!empty($existTestpaper)) {
            return $this->getTestpaperActivityDao()->update($existTestpaper[0]['id'], $newTestpaperFields);
        }

        return $this->getTestpaperActivityDao()->create($newTestpaperFields);
    }

    protected function getTestpaperActivityFields($testpaperActivity, $config)
    {
        return array(
            'testpaperId' => empty($config['testId']) ? 0 : $config['testId'],
            'doTimes' => $testpaperActivity['doTimes'],
            'redoInterval' => $testpaperActivity['redoInterval'],
            'limitedTime' => $testpaperActivity['limitedTime'],
            'checkType' => $testpaperActivity['checkType'],
            'requireCredit' => $testpaperActivity['requireCredit'],
            'testMode' => $testpaperActivity['testMode'],
            'finishCondition' => $testpaperActivity['finishCondition'],
            'syncId' => $testpaperActivity['id'],
        );
    }

    public function create($fields)
    {
        $fields = $this->filterFields($fields);

        return $this->getTestpaperActivityService()->createActivity($fields);
    }

    protected function filterFields($fields)
    {
        if (!empty($fields['finishType'])) {
            if ('score' == $fields['finishType']) {
                $testPaper = $this->getTestpaperService()->getTestpaper($fields['testpaperId']);
                $fields['finishCondition'] = array(
                    'type' => 'score',
                    'finishScore' => empty($fields['finishData']) ? 0 : round($testPaper['score'] * $fields['finishData'], 0),
                );
            } else {
                $fields['finishCondition'] = array();
            }
        }

        $filterFields = ArrayToolkit::parts(
            $fields,
            array(
                'testpaperId',
                'doTimes',
                'redoInterval',
                'length',
                'limitedTime',
                'checkType',
                'requireCredit',
                'testMode',
                'finishCondition',
                'syncId',
            )
        );

        if (isset($filterFields['length'])) {
            $filterFields['limitedTime'] = $filterFields['length'];
            unset($filterFields['length']);
        }

        if (isset($filterFields['doTimes']) && 0 == $filterFields['doTimes']) {
            $filterFields['testMode'] = 'normal';
        }

        $filterFields['mediaId'] = $filterFields['testpaperId'];
        unset($filterFields['testpaperId']);

        return $filterFields;
    }

    /**
     * @return TestpaperActivityService
     */
    protected function getTestpaperActivityService()
    {
        return $this->getBiz()->service('Activity:TestpaperActivityService');
    }

    /**
     * @return TestpaperActivityDao
     */
    protected function getTestpaperActivityDao()
    {
        return $this->createDao('Activity:TestpaperActivityDao');
    }
}
