<?php
namespace Topxia\Service\User\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\User\UpgradeNoticeService;

class UpgradeNoticeServiceImpl extends BaseService implements UpgradeNoticeService
{
    public function getNotice($id)
    {
        return $this->getUpgradeNoticeDao()->getNotice($id);
    }

    public function getNoticeByUserIdAndVersionAndCode($userId, $version, $code)
    {
        return $this->getUpgradeNoticeDao()->getNoticeByUserIdAndVersionAndCode($userId, $version, $code);
    }

    public function addNotice($fields)
    {
        $fields['createdTime'] = time();
        return $this->getUpgradeNoticeDao()->addNotice($fields);
    }

    protected function getUpgradeNoticeDao()
    {
        return $this->createDao('User.UpgradeNoticeDao');
    }
}
