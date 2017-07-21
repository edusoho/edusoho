<?php

namespace Biz\Course\Component\Clones\Chain;

use Biz\Activity\Config\Activity;
use Biz\Activity\Dao\ActivityDao;
use Biz\Activity\Service\TestpaperActivityService;
use Biz\Course\Component\Clones\AbstractClone;
use Biz\Testpaper\Service\TestpaperService;

class ActivityTestpaperClone extends AbstractClone
{
    protected function cloneEntity($source, $options)
    {
        return $this->cloneActivityTestpapers($source, $options);
    }

    private function cloneActivityTestpapers($source, $options)
    {
        $newActivity = $options['newActivity'];
        $newCourseSet = $options['newCourseSet'];
        $newCourse = $options['newCourse'];
        $testpaperActivity = $this->getActivityConfig($source['mediaType'])->get($source['mediaId']);
        $testpaperId = $testpaperActivity['mediaId'];
        $testpaper = $this->getTestpaperService()->getTestpaperByIdAndType($testpaperId, $source['mediaType']);
        if (empty($testpaper)) {
            return null;
        }

        $existed = $this->getTestpaperService()->getTestpaperByCopyIdAndCourseSetId($testpaperId, $newCourseSet['id']);

        if (!empty($existed)) {
            return $existed;
        }
        $newTestpaper = $this->filterFields($testpaperActivity);
        $newTestpaper['courseSetId'] = $newCourseSet['id'];
        $newTestpaper['courseId'] = $newCourse['id'];

        $newTestpaper = $this->getTestpaperService()->updateTestpaper($existed['id'], $newTestpaper);

        //create activity config
        $config = $this->getActivityConfig($newActivity['mediaType']);

        $ext = $config->copy($source, array(
            'refLiveroom' => $source['fromCourseSetId'] != $options['sourceCourseSet']['id'],
            'testId' => $newTestpaper[''],
            'newActivity' => $newActivity,
            'isCopy' => 0,
        ));

        $this->getActivityDao()->update($newActivity['id'], array('mediaId' => $ext['id']));

        return $newTestpaper;
    }

    protected function getFields()
    {
        return array(
            'name',
            'description',
            'limitedTime',
            'pattern',
            'status',
            'score',
            'passedCondition',
            'itemCount',
            'metas',
            'type',
        );
    }

    public function getActivityConfig($type)
    {
        return $this->biz["activity_type.{$type}"];
    }

    /**
     * @return TestpaperActivityService
     */
    protected function getTestpaperActivityService()
    {
        return $this->biz->service('Activity:TestpaperActivityService');
    }

    /**
     * @return TestpaperService
     */
    protected function getTestpaperService()
    {
        return $this->biz->service('Testpaper:TestpaperService');
    }

    /**
     * @return ActivityDao
     */
    private function getActivityDao()
    {
        return $this->biz->dao('Activity:ActivityDao');
    }
}
