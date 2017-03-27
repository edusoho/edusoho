<?php

namespace AppBundle\Extensions\DataTag;

use AppBundle\Common\ArrayToolkit;

class CourseThreadsByTypeDataTag extends CourseBaseDataTag implements DataTag
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
            $type = array();
        } else {
            $type = $arguments['type'];
        }

        $arguments['status'] = '1';

        $threads = $this->getThreadService()->searchThreads(array('type' => $type, 'private' => 0), 'posted', 0, $arguments['count']);

        $courseIds = ArrayToolkit::column($threads, 'courseId');
        $courses = $this->getCourseService()->findCoursesByIds($courseIds);
        $courses = ArrayToolkit::index($courses, 'id');
        $courseSets = $this->getCourseSetService()->findCourseSetsByCourseIds($courseIds);
        $courseSets = ArrayToolkit::index($courseSets, 'id');

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($threads, 'userId'));

        $latestPostUsers = $this->getUserService()->findUsersByIds(ArrayToolkit::column($threads, 'latestPostUserId'));

        foreach ($threads as $key => $thread) {
            if (isset($courses[$thread['courseId']], $courseSets[$thread['courseId']]['courseSetId'])) {
                $threads[$key]['course'] = $courses[$thread['courseId']];
                $threads[$key]['courseSet'] = $courseSets[$threads[$key]['course']['courseSetId']];
            }

            if (isset($users[$thread['userId']]) && $thread['userId'] == $users[$thread['userId']]['id']) {
                $threads[$key]['user'] = $users[$thread['userId']];
            }

            if ($thread['latestPostUserId'] == $latestPostUsers[$thread['latestPostUserId']]['id']) {
                $threads[$key]['latestPostUser'] = $latestPostUsers[$thread['latestPostUserId']];
            }
        }

        return $threads;
    }
}
