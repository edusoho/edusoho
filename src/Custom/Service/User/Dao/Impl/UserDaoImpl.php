<?php
/**
 * Created by PhpStorm.
 * User: retamia
 * Date: 15/9/17
 * Time: 14:02
 */

namespace Custom\Service\User\Dao\Impl;

use Custom\Service\User\Dao\UserDao;
use Topxia\Service\User\Dao\Impl\UserDaoImpl as BaseUserDao;



class UserDaoImpl extends BaseUserDao implements UserDao
{

    public function getUserByStaffNo($staffNo)
    {
        $sql = "SELECT * FROM {$this->table} WHERE staffNo = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($staffNo));
    }

    protected function createUserQueryBuilder($conditions)
    {
        if(isset($conditions['keywordType']) && isset($conditions['keyword'])) {
            $conditions[$conditions['keywordType']]=$conditions['keyword'];
            unset($conditions['keywordType']);
            unset($conditions['keyword']);
        }

        if(isset($conditions['staffNo']))
        {
            $conditions['staffNo'] = "%{$conditions['staffNo']}%";
        }

        $builder = parent::createUserQueryBuilder($conditions)
            ->andWhere('staffNo LIKE :staffNo');

        return $builder;
    }
}