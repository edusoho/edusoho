<?php

namespace AppBundle\Extensions\DataTag;

class OpenCoursesDataTag extends CourseBaseDataTag implements DataTag
{
    /**
     * 获取公开课列表.
     *
     * 可传入的参数：
     *   count      必需 课程数量
     *   orderType  可选 排序规则
     *   categoryId 可选 分类ID
     *
     * @param array $arguments 参数
     *
     * @return array 课程列表
     */
    public function getData(array $arguments)
    {
        $this->checkCount($arguments);
        list($conditions, $orderBy) = $this->filterConditions($arguments);

        $courses = $this->getOpenCourseService()->searchCourses($conditions, $orderBy, 0, $arguments['count']);

        if (!empty($arguments['orderBy']) && 'recommendedSeq' == $arguments['orderBy']) {
            if (count($courses) < $arguments['count']) {
                $unrecommendedCourses = $this->getOpenCourseService()->searchCourses([
                    'status' => 'published   ',
                    'recommended' => 0,
                ], ['createdTime' => 'DESC'],
                    0, ($arguments['count'] - count($courses))
                );

                $courses = array_merge($courses, $unrecommendedCourses);
            }
        }

        return $this->getCourseTeachersAndCategories($courses);
    }

    protected function filterConditions($arguments)
    {
        $conditions = ['status' => 'published'];
        $orderBy = ['recommendedSeq' => 'ASC'];

        if (!empty($arguments['orderBy']) && 'recommendedSeq' == $arguments['orderBy']) {
            $conditions['recommended'] = 1;
            $orderBy = ['recommendedSeq' => 'ASC'];
        } elseif (!empty($arguments['orderBy']) && 'hitNum' == $arguments['orderBy']) {
            $orderBy = ['hitNum' => 'DESC'];
        }

        if (!empty($arguments['categoryId'])) {
            $conditions['categoryId'] = $arguments['categoryId'];
        }

        return [$conditions, $orderBy];
    }

    protected function getOpenCourseService()
    {
        return $this->getServiceKernel()->getBiz()->service('OpenCourse:OpenCourseService');
    }
}
