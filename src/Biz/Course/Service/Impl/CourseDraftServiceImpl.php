<?php

namespace Biz\Course\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Course\CourseDraftException;
use Biz\Course\Dao\CourseDraftDao;
use Biz\Course\Service\CourseDraftService;

class CourseDraftServiceImpl extends BaseService implements CourseDraftService
{
    public function getCourseDraft($id)
    {
        return $this->getCourseDraftDao()->get($id);
    }

    public function getCourseDraftByCourseIdAndActivityIdAndUserId($courseId, $activityId, $userId)
    {
        $draft = $this->getCourseDraftDao()->getByCourseIdAndActivityIdAndUserId($courseId, $activityId, $userId);

        if (empty($draft) || ($draft['userId'] != $userId)) {
            return array();
        }

        return $draft;
    }

    public function createCourseDraft($draft)
    {
        $draft = ArrayToolkit::parts(
            $draft,
            array('userId', 'title', 'courseId', 'summary', 'content', 'activityId', 'createdTime')
        );
        $draft['userId'] = $this->getCurrentUser()->id;
        $draft['createdTime'] = time();

        return $this->getCourseDraftDao()->create($draft);
    }

    public function updateCourseDraft($id, $fields)
    {
        $draft = $this->getCourseDraft($id);

        if (empty($draft)) {
            $this->createNewException(CourseDraftException::NOTFOUND_DRAFT());
        }

        $fields = $this->_filterDraftFields($fields);

        return $this->getCourseDraftDao()->update($id, $fields);
    }

    public function deleteCourseDrafts($courseId, $activityId, $userId)
    {
        return $this->getCourseDraftDao()->deleteCourseDrafts($courseId, $activityId, $userId);
    }

    protected function _filterDraftFields($fields)
    {
        $fields = ArrayToolkit::filter(
            $fields,
            array(
                'title' => '',
                'summary' => '',
                'content' => '',
                'createdTime' => 0,
            )
        );

        return $fields;
    }

    /**
     * @return CourseDraftDao
     */
    protected function getCourseDraftDao()
    {
        return $this->createDao('Course:CourseDraftDao');
    }

    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }
}
