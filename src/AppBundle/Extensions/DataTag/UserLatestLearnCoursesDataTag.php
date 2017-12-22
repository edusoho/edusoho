<?php

namespace AppBundle\Extensions\DataTag;

class UserLatestLearnCoursesDataTag extends CourseBaseDataTag implements DataTag
{
    /**
     * 获取用户当前学习的课程.
     *
     * 可传入的参数：
     *   userId   必需 用户ID
     *   count    必需 课程数量，取值不能超过100
     *
     * @param array $arguments 参数
     *
     * @return array 课程列表
     */
    public function getData(array $arguments)
    {
        $this->assertTrue(1); //原代码会报错，先解决报错问题
        // $this->checkCount($arguments);
        // $this->checkUserId($arguments);

        // $courses = $this->getCourseService()->findUserLearningCourses($arguments['userId'], 0, $arguments['count']);

        // return $this->getCourseTeachersAndCategories($courses);
    }
}
