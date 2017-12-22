<?php


require_once 'dao/text_activity_dao.php';

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Service\ActivityService;
use Biz\Course\Service\CourseDraftService;
use text\dao\text_activity_dao;


class activity_text extends \Biz\Activity\BaseActivityExt
{
    public function create($fields)
    {
        $text = ArrayToolkit::parts(
            $fields,
            array(
                'finishType',
                'finishDetail',
            )
        );
        $biz = $this->getBiz();
        $text['createdUserId'] = $biz['user']['id'];

        $this->getCourseDraftService()->deleteCourseDrafts($fields['fromCourseId'], 0, $biz['user']['id']);

        return $this->getTextActivityDao()->create($text);
    }

    /**
     * @return text\dao\text_activity_dao
     */
    protected function getTextActivityDao()
    {
        return new text_activity_dao($this->getBiz());
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->getBiz()->service('Activity:ActivityService');
    }

    /**
     * @return CourseDraftService
     */
    protected function getCourseDraftService()
    {
        return $this->getBiz()->service('Course:CourseDraftService');
    }
}