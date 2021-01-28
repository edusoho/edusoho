<?php

namespace AppBundle\Extensions\DataTag;

class PromotedTeacherDataTag extends CourseBaseDataTag implements DataTag
{
    /**
     * 获取一个教师.
     *
     * @param array $arguments 参数
     *
     * @return array 用户
     */
    public function getData(array $arguments)
    {
        $teacher = $this->getUserService()->findLatestPromotedTeacher(0, 1);

        unset($teacher['password']);
        unset($teacher['salt']);

        if ($teacher) {
            $teacher = $teacher[0];
            $teacher = array_merge(
                $teacher,
                $this->getUserService()->getUserProfile($teacher['id'])
            );
        }

        return $teacher;
    }
}
