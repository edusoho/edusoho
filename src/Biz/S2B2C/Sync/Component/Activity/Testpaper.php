<?php

namespace Biz\S2B2C\Sync\Component\Activity;

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Dao\TestpaperActivityDao;
use Biz\Activity\Service\TestpaperActivityService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerSceneService;

class Testpaper extends Activity
{
    public function sync($activity, $config = [])
    {
        if ('testpaper' !== $activity['mediaType']) {
            return null;
        }

        $testpaperActivity = $activity[$activity['mediaType'].'Activity'];
        if (empty($testpaperActivity)) {
            return [];
        }

        $newExt = $this->getTestpaperActivityFields($testpaperActivity, $config);

        $scene = $this->createScene($activity, $newExt);
        $newExt['answerSceneId'] = $scene['id'];

        return $this->create($newExt);
    }

    public function updateToLastedVersion($activity, $config = [])
    {
        if ('testpaper' !== $activity['mediaType']) {
            return null;
        }

        $testpaperActivity = $activity[$activity['mediaType'].'Activity'];
        if (empty($testpaperActivity)) {
            return [];
        }
        $newExt = $this->getTestpaperActivityFields($testpaperActivity, $config);
        $newTestpaperFields = $this->filterFields($newExt);
        $scene = $this->createScene($activity, $newExt);
        $newTestpaperFields['answerSceneId'] = $scene['id'];

        $existTestpaper = $this->getTestpaperActivityDao()->search(['syncId' => $newTestpaperFields['syncId']], [], 0, PHP_INT_MAX);
        if (!empty($existTestpaper)) {
            return $this->getTestpaperActivityDao()->update($existTestpaper[0]['id'], $newTestpaperFields);
        }

        return $this->getTestpaperActivityDao()->create($newTestpaperFields);
    }

    protected function getTestpaperActivityFields($testpaperActivity, $config)
    {
        return [
            'testpaperId' => empty($config['testId']) ? 0 : $config['testId'],
            'doTimes' => $testpaperActivity['doTimes'],
            'redoInterval' => $testpaperActivity['redoInterval'],
            'limitedTime' => $testpaperActivity['limitedTime'],
            'checkType' => $testpaperActivity['checkType'],
            'requireCredit' => $testpaperActivity['requireCredit'],
            'testMode' => $testpaperActivity['testMode'],
            'finishCondition' => $testpaperActivity['finishCondition'],
            'syncId' => $testpaperActivity['id'],
        ];
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
                $fields['finishCondition'] = [
                    'type' => 'score',
                    'finishScore' => empty($fields['finishData']) ? 0 : round($testPaper['score'] * $fields['finishData'], 0),
                ];
            } else {
                $fields['finishCondition'] = [];
            }
        }

        $filterFields = ArrayToolkit::parts(
            $fields,
            [
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
            ]
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

    protected function createScene($activity, $ext)
    {
        $assessment = $activity['assessment'];
        $passScore = 0;
        if ($assessment) {
            $passScore = intval($assessment['total_score'] * $activity['finishData']);
        }

        return $this->getAnswerSceneService()->create([
            'id' => $activity['id'],
            'name' => $activity['title'],
            'limited_time' => $ext['limitedTime'],
            'do_times' => $ext['doTimes'],
            'redo_interval' => $ext['redoInterval'] * 60,
            'need_score' => 1,
            'manual_marking' => 1,
            'start_time' => $activity['startTime'],
            'pass_score' => $passScore,
            'enable_facein' => 0,
        ]);
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

    /**
     * @return AnswerSceneService
     */
    protected function getAnswerSceneService()
    {
        return $this->getBiz()->service('ItemBank:Answer:AnswerSceneService');
    }
}
