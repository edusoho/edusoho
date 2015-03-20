<?php

namespace Topxia\DataTag;

use Topxia\DataTag\DataTag;
use Topxia\Common\ArrayToolkit;

class LatestFinishedLearnsDataTag extends BaseDataTag implements DataTag  
{

    /**
     * 获取最近完成学习列表
     *
     * 可传入的参数：
     *   count    必需 课程数量，取值不能超过100
     * 
     * @param  array $arguments 参数
     * @return array 课程列表
     */
    public function getData(array $arguments)
    {
        if (empty($arguments['count'])) {
            $arguments['count'] = 5;
        }   

        $learns = $this->getCourseService()->findLatestFinishedLearns(0, $arguments['count']);

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($learns, 'userId'));

        $lessons = $this->getCourseService()->findLessonsByIds(ArrayToolkit::column($learns, 'lessonId'));

        foreach ($learns as $key => $learn) {
            if ($learn['userId'] == $users[$learn['userId']]['id']) {

                $learns[$key]['user'] = $users[$learn['userId']];
            }

            if (!empty($lessons[$learn['lessonId']]['id']) && $learn['lessonId'] == $lessons[$learn['lessonId']]['id']) {

                $learns[$key]['lesson'] = $lessons[$learn['lessonId']];
            }
        }

        return $learns;
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

}
