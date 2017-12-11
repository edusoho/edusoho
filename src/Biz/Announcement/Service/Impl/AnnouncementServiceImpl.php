<?php

namespace Biz\Announcement\Service\Impl;

use Biz\BaseService;
use Biz\System\Service\LogService;
use Biz\Announcement\Dao\AnnouncementDao;
use Biz\Announcement\Service\AnnouncementService;
use AppBundle\Common\ArrayToolkit;

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
        return $this->getAnnouncementDao()->count($conditions);
    }

    public function createAnnouncement($announcement)
    {
        if (!ArrayToolkit::requireds($announcement, array('content', 'startTime', 'endTime'), true)) {
            throw $this->createInvalidArgumentException('Arguments invalid');
        }

        if (isset($announcement['notify'])) {
            unset($announcement['notify']);
        }

        $announcement['content'] = $this->biz['html_helper']->purify(empty($announcement['content']) ? '' : $announcement['content']);

        $announcement['userId'] = $this->getCurrentUser()->id;
        $announcement['createdTime'] = time();
        $announcement = $this->fillOrgId($announcement);
        $announcement = $this->getAnnouncementDao()->create($announcement);
        if ($announcement['targetType'] == 'global') {
            $this->dispatchEvent('announcement.create', $announcement);
        }

        return $announcement;
    }

    public function updateAnnouncement($id, $announcement)
    {
        if (!ArrayToolkit::requireds($announcement, array('content', 'startTime', 'endTime'), true)) {
            throw $this->createInvalidArgumentException('Arguments invalid');
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
            throw $this->createNotFoundException(sprintf('公告#%s不存在。', $id));
        }

        $this->getAnnouncementDao()->delete($id);

        $content = strip_tags($announcement['content']);
        $this->getLogService()->info('announcement', 'delete', "删除{$announcement['targetType']}(#{$announcement['targetId']})的公告《{$content}》(#{$announcement['id']})");

        $this->dispatchEvent('announcement.delete', $announcement);

        return true;
    }

    public function batchUpdateOrg($ids, $orgCode)
    {
        if (!is_array($ids)) {
            $ids = array($ids);
        }
        $fields = $this->fillOrgId(array('orgCode' => $orgCode));

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
        $targetType = array('course', 'classroom', 'global');
        if (!in_array($conditions['targetType'], $targetType)) {
            throw $this->createServiceException('targetType不正确！');
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
