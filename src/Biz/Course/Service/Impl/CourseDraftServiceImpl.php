<?php

namespace Biz\Course\Service\Impl;

use Biz\BaseService;
use Biz\Course\Service\CourseDraftService;

class CourseDraftServiceImpl extends BaseService implements CourseDraftService
{
    public function getCourseDraft($id)
    {
        return $this->getCourseDraftDao()->get($id);
    }

    public function findCourseDraft($courseId, $lessonId, $userId)
    {
        $draft = $this->getCourseDraftDao()->findCourseDraft($courseId, $lessonId, $userId);

        if (empty($draft) || ($draft['userId'] != $userId)) {
            return null;
        }

        return $draft;
    }

    public function createCourseDraft($draft)
    {
        $draft                = ArrayToolkit::parts($draft, array('userId', 'title', 'courseId', 'summary', 'content', 'lessonId', 'createdTime'));
        $draft['userId']      = $this->getCurrentUser()->id;
        $draft['createdTime'] = time();
        $draft                = $this->getCourseDraftDao()->create($draft);
        return $draft;
    }

    public function updateCourseDraft($courseId, $lessonId, $userId, $fields)
    {
        $draft = $this->findCourseDraft($courseId, $lessonId, $userId);

        if (empty($draft)) {
            throw $this->createServiceException($this->getKernel()->trans('草稿不存在，更新失败！'));
        }

        $fields = $this->_filterDraftFields($fields);

        $this->getLogService()->info('course', 'update_draft', "更新草稿《{$draft['title']}》(#{$draft['id']})的信息", $fields);

        return $this->getCourseDraftDao()->update($courseId, $lessonId, $userId, $fields);
    }

    public function deleteCourseDrafts($courseId, $lessonId, $userId)
    {
        return $this->getCourseDraftDao()->deleteCourseDrafts($courseId, $lessonId, $userId);
    }

    protected function _filterDraftFields($fields)
    {
        $fields = ArrayToolkit::filter($fields, array(
            'title'       => '',
            'summary'     => '',
            'content'     => '',
            'createdTime' => 0
        ));
        return $fields;
    }

    protected function getCourseDraftDao()
    {
        return $this->createDao('Course:CourseDraftDao');
    }

    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }
}
