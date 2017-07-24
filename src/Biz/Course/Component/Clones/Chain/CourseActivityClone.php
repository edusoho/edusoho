<?php

namespace Biz\Course\Component\Clones\Chain;

use Biz\Activity\Config\Activity;
use Biz\Activity\Dao\ActivityDao;
use Biz\Course\Component\Clones\AbstractClone;
use Biz\Testpaper\Dao\TestpaperDao;
use Codeages\Biz\Framework\Context\Biz;

class CourseActivityClone extends AbstractClone
{
    public function __construct(Biz $biz, array $processNodes = array(), $auto = false)
    {
        $processNodes = array(
            'activity-material' => array(
                'class' => 'Biz\Course\Component\Clones\Chain\ActivityMaterialClone',
                'priority' => 100,
            ),
//            'activity-testpaper' => array(
//                'class' => 'Biz\Course\Component\Clones\Chain\ActivityTestpaperClone',
//                'priority' => 90,
//            ),
        );
        parent::__construct($biz, $processNodes, $auto);
    }

    protected function cloneEntity($source, $options)
    {
        return $this->cloneCourseActivity($source, $options);
    }

    protected function getFields()
    {
        return array(
            'mediaType',
            'title',
            'remark',
            'content',
            'length',
            'startTime',
            'endTime',
        );
    }

    private function cloneCourseActivity($source, $options)
    {
        $newCourse = $options['newCourse'];
        $newCourseSet = $options['newCourseSet'];
        $activities = $this->getActivityDao()->findByCourseId($source['id']);
        if (empty($activities)) {
            return array();
        }
        $activityMap = array();
        foreach ($activities as $activity) {
            $newActivity = $this->filterFields($activity);

            $newActivity['fromUserId'] = $this->biz['user']['id'];
            $newActivity['fromCourseId'] = $newCourse['id'];
            $newActivity['fromCourseSetId'] = $newCourseSet['id'];
            $newActivity['copyId'] = $activity['id'];

            $config = $this->getActivityConfig($activity['mediaType']);
            $testId = 0;
            if (in_array($activity['mediaType'], array('testpaper'))) {
                $originalActivityTestpaper = $config->get($activity['mediaId']);
                $activityTestpaper = $this->getTestpaperDao()->getTestpaperByCopyIdAndCourseSetId($originalActivityTestpaper['mediaId'], $newCourseSet['id']);
                $testId = $activityTestpaper['id'];
            }
            $ext = $config->copy($activity, array(
                'refLiveroom' => $activity['fromCourseSetId'] != $newCourseSet['id'],
                'testId' => $testId,
                'newActivity' => $newActivity,
                'isCopy' => false,
            ));

            if (!empty($ext)) {
                $newActivity['mediaId'] = $ext['id'];
            }

            if ($newActivity['mediaType'] == 'live') { //直播
                $newActivity['startTime'] = $activity['startTime'];
                $newActivity['endTime'] = $activity['endTime'];
            }

            $newActivity = $this->getActivityDao()->create($newActivity);
            $activityMap[$activity['id']] = $newActivity['id'];
            $options['newActivity'] = $newActivity;
            $this->doCourseCloneProcess($activity, $options);
        }

        return $activityMap;
    }

    private function doCourseCloneProcess($source, $options)
    {
        foreach ($this->processNodes as $processNode) {
            $class = new $processNode['class']($this->biz);
            $class->clones($source, $options);
        }
    }

    /**
     * @return TestpaperDao
     */
    protected function getTestpaperDao()
    {
        return $this->biz->dao('Testpaper:TestpaperDao');
    }

    /**
     * @return ActivityDao
     */
    private function getActivityDao()
    {
        return $this->biz->dao('Activity:ActivityDao');
    }

    /**
     * @param  $type
     *
     * @return Activity
     */
    private function getActivityConfig($type)
    {
        return $this->biz["activity_type.{$type}"];
    }
}
