<?php

namespace Biz\Marketing\Dao\Impl;

use Biz\Marketing\Dao\UserMarketingActivityDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class UserMarketingActivityDaoImpl extends AdvancedDaoImpl implements UserMarketingActivityDao
{
    protected $table = 'user_marketing_activity';

    public function findByJoinedIdAndType($joinedId, $type)
    {
        return $this->getByFields(array('joinedId' => $joinedId, 'type' => $type));
    }

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updatedTime'),
            'orderbys' => array('joinedTime'),
            'conditions' => array(
                'userId = :userId',
            ),
        );
    }
}
