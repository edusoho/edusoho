<?php
namespace Mooc\WebBundle\Extensions\DataTag;

use Topxia\WebBundle\Extensions\DataTag\DataTag;

class CreditDataTag extends BaseDataTag implements DataTag
{
    /**
     * 获取课程学分
     *
     * 可传入的参数：
     * courseId 必选 课程ID
     *
     * @param  array $arguments     参数
     * @return array 课程列表
     */

    public function getData(array $arguments)
    {
        $courseScoreSetting = $this->getCourseScoreService()->getScoreSettingByCourseId($arguments['courseId']);

        $credit = $courseScoreSetting['credit'];

        return $credit;
    }

    protected function getCourseScoreService()
    {
        return $this->getServiceKernel()->createService('Custom:Course.CourseScoreService');
    }
}
