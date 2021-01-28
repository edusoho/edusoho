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

        $userIds = array_merge(ArrayToolkit::column($threads, 'userId'), ArrayToolkit::column($threads, 'latestPostUserId'));

        $users = $this->getUserService()->findUsersByIds($userIds);

        foreach ($threads as $key => $thread) {
            if (isset($courses[$thread['courseId']], $courseSets[$thread['courseSetId']])) {
                $threads[$key]['course'] = $courses[$thread['courseId']];
                $threads[$key]['courseSet'] = $courseSets[$thread['courseSetId']];
            }

            if (isset($users[$thread['userId']]) && $thread['userId'] == $users[$thread['userId']]['id']) {
                $threads[$key]['user'] = $users[$thread['userId']];
            }

            if (!empty($thread['latestPostUserId']) && isset($users[$thread['latestPostUserId']])) {
                $threads[$key]['latestPostUser'] = $users[$thread['latestPostUserId']];
            }
        }

        return $threads;
    }
}
