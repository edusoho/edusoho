<?php

namespace Biz\DestroyAccount\Dao\Impl;

use Biz\DestroyAccount\Dao\DestroyedAccountDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class DestroyedAccountDaoImpl extends AdvancedDaoImpl implements DestroyedAccountDao
{
    protected $table = 'destroyed_account';

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updatedTime'),
            'orderbys' => array(
                'createdTime',
            ),
            'conditions' => array(
                'id = :id',
                'nickname like :nicknameLike',
            ),
        );
    }
}
