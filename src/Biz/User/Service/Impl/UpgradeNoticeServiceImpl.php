<?php

namespace Biz\User\Service\Impl;

use Biz\BaseService;
use Biz\User\Service\UpgradeNoticeService;

class UpgradeNoticeServiceImpl extends BaseService implements UpgradeNoticeService
{
    public function getNotice($id)
    {
        return $this->getUpgradeNoticeDao()->get($id);
    }

    public function getNoticeByUserIdAndVersionAndCode($userId, $version, $code)
    {
        return $this->getUpgradeNoticeDao()->getByUserIdAndVersionAndCode($userId, $version, $code);
    }

    public function addNotice($fields)
    {
        $fields['createdTime'] = time();

        return $this->getUpgradeNoticeDao()->create($fields);
    }

    protected function getUpgradeNoticeDao()
    {
        return $this->createDao('User:UpgradeNoticeDao');
    }
}
