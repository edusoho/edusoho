<?php

namespace Biz\Announcement\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\Announcement\AnnouncementException;
use Biz\Announcement\Dao\AnnouncementDao;
use Biz\Announcement\Service\AnnouncementService;
use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\System\Service\LogService;

class AnnouncementServiceImpl extends BaseService implements AnnouncementService
{
    public function getAnnouncement($id)
    {
        return $this->getAnnouncementDao()->get($id);
    }

    public function searchAnnouncements($conditions, $orderBy, $start, $limit)
    {
        $conditions = $this->_prepareSearchConditions($conditions);

        return $this->getAnnouncementDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function countAnnouncements($conditions)
    {
        $conditions = $this->_prepareSearchConditions($conditions);

        return $this->getAnnouncementDao()->count($conditions);
    }

    public function createAnnouncement($announcement)
    {
        if (!ArrayToolkit::requireds($announcement, ['content', 'startTime', 'endTime'], true)) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        if (isset($announcement['notify'])) {
            unset($announcement['notify']);
        }

        $announcement['content'] = $this->purifyHtml(empty($announcement['content']) ? '' : $announcement['content']);

        $announcement['userId'] = $this->getCurrentUser()->id;
        $announcement['createdTime'] = time();
        $announcement = $this->fillOrgId($announcement);
        $announcement = $this->getAnnouncementDao()->create($announcement);
        if ('global' == $announcement['targetType']) {
            $this->dispatchEvent('announcement.create', $announcement);
        }

        return $announcement;
    }

    public function updateAnnouncement($id, $announcement)
    {
        if (!ArrayToolkit::requireds($announcement, ['content', 'startTime', 'endTime'], true)) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $announcement = $this->fillOrgId($announcement);
        $announcement['updatedTime'] = time();

        $announcement = $this->getAnnouncementDao()->update($id, $announcement);

        $this->dispatchEvent('announcement.update', $announcement);

        return $announcement;
    }

    public function deleteAnnouncement($id)
    {
        $announcement = $this->getAnnouncement($id);
        if (empty($announcement)) {
            $this->createNewException(AnnouncementException::ANNOUNCEMENT_NOT_FOUND());
        }

        $this->getAnnouncementDao()->delete($id);

        $content = strip_tags($announcement['content']);

        $this->dispatchEvent('announcement.delete', $announcement);

        return true;
    }

    public function batchUpdateOrg($ids, $orgCode)
    {
        if (!is_array($ids)) {
            $ids = [$ids];
        }
        $fields = $this->fillOrgId(['orgCode' => $orgCode]);

        foreach ($ids as $id) {
            $this->getAnnouncementDao()->update($id, $fields);
        }
    }

    /**
     * @return AnnouncementDao
     */
    protected function getAnnouncementDao()
    {
        return $this->createDao('Announcement:AnnouncementDao');
    }

    protected function _prepareSearchConditions($conditions)
    {
        $targetType = ['course', 'classroom', 'global'];
        if (!empty($conditions['targetType']) && !in_array($conditions['targetType'], $targetType)) {
            $this->createNewException(AnnouncementException::TYPE_INVALID());
        }

        if (!isset($conditions['likeOrgCode']) && !isset($conditions['orgCode']) && !isset($conditions['orgId'])) {
            $conditions['orgCode'] = $this->getCurrentUser()->getSelectOrgCode();
        }

        if (isset($conditions['likeOrgCode']) && !empty($conditions['likeOrgCode'])) {
            $conditions['orgCode'] = $conditions['likeOrgCode'];
            unset($conditions['likeOrgCode']);
        }

        return $conditions;
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->biz->service('System:LogService');
    }
}
