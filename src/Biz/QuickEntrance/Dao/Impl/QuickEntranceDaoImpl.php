<?php

namespace Biz\QuickEntrance\Dao\Impl;

use Biz\QuickEntrance\Dao\QuickEntranceDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class QuickEntranceDaoImpl extends AdvancedDaoImpl implements QuickEntranceDao
{
    protected $table = 'quick_entrance';

    public function getByUserId($userId)
    {
        $entrance = $this->getByFields(array('userId' => $userId));
        $entrance['data'] = json_decode($entrance['data']);

        return $entrance;
    }

    public function declares()
    {
        return array(
            'conditions' => array(
                'userId = :userId',
            ),
        );
    }
}
