<?php
namespace Mooc\WebBundle\Extensions\DataTag;

use Mooc\WebBundle\Extensions\DataTag\BaseDataTag;

/**
 * 获取课程评分设置
 */
class CourseScoreSettingDataTag extends BaseDataTag
{
    public function getData(array $arguments)
    {
        if (isset($arguments['courseId'])) {
            return $this->getCourseScoreService()->getScoreSettingByCourseId($arguments['courseId']);
        }
    }

    protected function getCourseScoreService()
    {
        return $this->createService('Mooc:Course.CourseScoreService');
    }
}
