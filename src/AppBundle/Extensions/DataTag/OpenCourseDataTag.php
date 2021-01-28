<?php

namespace AppBundle\Extensions\DataTag;

use Topxia\Service\Common\ServiceKernel;

class OpenCourseDataTag extends BaseDataTag implements DataTag
{
    /**
     * 获取单个公开课.
     *
     * 可传入的参数：
     *   courseId 必需 课程ID
     *
     * 该DataTag返回了单个公开课对象
     *
     * @param array $arguments 参数
     *
     * @return array 栏目
     */
    public function getData(array $arguments)
    {
        $course = $this->getOpenCourseService()->getCourse($arguments['courseId']);

        if ($course) {
            $course['teachers'] = empty($course['teacherIds']) ? array() : $this->getUserService()->findUsersByIds($course['teacherIds']);

            if ($course['categoryId'] != '0') {
                $course['category'] = $this->getCategoryService()->getCategory($course['categoryId']);
            }
        }

        return $course;
    }

    protected function getOpenCourseService()
    {
        return $this->getServiceKernel()->createService('OpenCourse:OpenCourseService');
    }

    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User:UserService');
    }

    protected function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy:CategoryService');
    }
}
