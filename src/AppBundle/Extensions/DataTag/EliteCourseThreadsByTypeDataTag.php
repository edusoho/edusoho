<?php

namespace AppBundle\Extensions\DataTag;

use AppBundle\Common\ArrayToolkit;

class EliteCourseThreadsByTypeDataTag extends CourseBaseDataTag implements DataTag
{
    /**
     * 获取加精的课程话题列表.
     *
     * 可传入的参数：
     *   type 选填 话题类型
     *   count 必需 课程话题数量，取值不能超过100
     *
     * @param array $arguments 参数
     *
     * @return array 课程话题
     */
    public function getData(array $arguments)
    {
        $this->checkCount($arguments);

        if (empty($arguments['type'])) {
            $type = '';
        } else {
            $type = $arguments['type'];
        }

        $arguments['status'] = '1';

        $threads = $this->getThreadService()->findEliteThreadsByType($type, $arguments['status'], 0, $arguments['count']);

        $courses = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($threads, 'courseId'));

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($threads, 'userId'));

        $latestPostUsers = $this->getUserService()->findUsersByIds(ArrayToolkit::column($threads, 'latestPostUserId'));

        foreach ($threads as $key => $thread) {
            if ($thread['courseId'] == $courses[$thread['courseId']]['id']) {
                $threads[$key]['course'] = $courses[$thread['courseId']];
            }

            if ($thread['userId'] == $users[$thread['userId']]['id']) {
                $threads[$key]['user'] = $users[$thread['userId']];
            }

            if ($thread['latestPostUserId'] == $latestPostUsers[$thread['latestPostUserId']]['id']) {
                $threads[$key]['latestPostUser'] = $latestPostUsers[$thread['latestPostUserId']];
            }
        }

        return $threads;
    }
}
