<?php

namespace Biz\Testpaper\Job;

use Codeages\Biz\Framework\Scheduler\AbstractJob;

class UpdateRealTimeTestResultStatusJob extends AbstractJob
{
    public function execute()
    {
        if (empty($this->args['targetId']) || empty($this->args['targetType']) || $this->args['targetType'] != 'activity') {
            return;
        }

        $activity = $this->getActivityService()->getActivity($this->args['targetId'], true);

        if (empty($activity)) {
            return;
        }

        if ($activity['mediaType'] != 'testpaper' || empty($activity['ext']['testMode']) || $activity['ext']['testMode'] != 'realTime' || empty($activity['ext']['limitedTime'])) {
            return;
        }

        $conditions = array(
            'courseId' => $activity['fromCourseId'],
            'lessonId' => $activity['id'],
            'status' => 'doing',
        );
        $results = $this->getTestpaperService()->searchTestpaperResults(
            $conditions,
            null,
            0,
            PHP_INT_MAX
        );

        if (empty($results)) {
            return;
        }

        $testpaperId = $activity['ext']['mediaId'];
        $conditions = array(
            'testId' => $testpaperId,
            'questionTypes' => array('essay'),
            'type' => 'testpaper',
        );

        $itemCount = $this->getTestpaperService()->searchItemCount($conditions);
        $status = $itemCount > 0 ? 'reviewing' : 'finished';

        foreach ($results as $result) {
            $this->getTestpaperService()->updateTestpaperResult($result['id'], array('status' => $status));
        }
    }

    protected function getActivityService()
    {
        return $this->biz->service('Activity:ActivityService');
    }

    protected function getTestpaperService()
    {
        return $this->biz->service('Testpaper:TestpaperService');
    }
}
