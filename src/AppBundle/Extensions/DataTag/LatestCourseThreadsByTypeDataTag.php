<?php

namespace AppBundle\Extensions\DataTag;

use AppBundle\Common\ArrayToolkit;

/**
 * @todo  去除，采用LatestCourseThreadsDataTag
 */
class LatestCourseThreadsByTypeDataTag extends CourseBaseDataTag implements DataTag
{
    /**
     * 获取最新发表的课程话题列表.
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

        //查询条件空置会被过滤，会查找出全部的话题
        if (empty($arguments['type'])) {
            $type = '';
        } else {
            $type = $arguments['type'];
        }
        $threads = $this->getThreadService()->findLatestThreadsByType($type, 0, $arguments['count']);

        $courses = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($threads, 'courseId'));

        foreach ($threads as $key => $thread) {
            if ($thread['courseId'] == $courses[$thread['courseId']]['id']) {
                $threads[$key]['courseTitle'] = $courses[$thread['courseId']]['title'];
            }
        }

        return $threads;
    }
}
