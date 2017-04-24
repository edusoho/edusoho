<?php

namespace Biz\User\Dao\Impl;

use Biz\User\Dao\UpgradeNoticeDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class UpgradeNoticeDaoImpl extends GeneralDaoImpl implements UpgradeNoticeDao
{
    protected $table = 'upgrade_notice';

    public function getByUserIdAndVersionAndCode($userId, $version, $code)
    {
        return $this->getByFields(array(
            'userId' => $userId,
            'version' => $version,
            'code' => $code,
        ));
    }

    public function declares()
    {
        return array(
            'conditions' => array(
                'userId = :userId',
                'version = :version',
                'code = :code',
            ),
        );
    }
}
