<?php

namespace Topxia\WebBundle\Extensions\DataTag;

use Topxia\WebBundle\Extensions\DataTag\DataTag;

class BeginningLiveLessonDataTag extends BaseDataTag implements DataTag
{
    /**
     * 根据当前用户获取一个即将直播的课时
     *
     * 可传入的参数：
     *   afterSecond 必需 即将在多少秒后直播
     *
     * @param  array $arguments 参数
     * @return array 课时
     */

    public function getData(array $arguments)
    {
        $liveLesson = $this->getLiveCourseService()->findBeginingLiveCourse($arguments['afterSecond']);
        return $liveLesson;
    }

    protected function getLiveCourseService()
    {
        return $this->getServiceKernel()->createService('Course.LiveCourseService');
    }
}
