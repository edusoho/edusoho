<?php
namespace Mooc\Service\User\Dao\Impl;

use Mooc\Service\User\Dao\UserDao;
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

        if(isset($conditions['staffNo'])) {
            $conditions['staffNo'] = "%{$conditions['staffNo']}%";
        }

        $builder = parent::createUserQueryBuilder($conditions)
            ->andWhere('staffNo LIKE :staffNo')
            ->andWhere('organizationId IN (:organizationIds)');

        return $builder;
    }

    public function resetUserOrganizationId($organizationId)
    {
        $sql = "UPDATE {$this->table} SET organizationId = 0 WHERE organizationId = ?;";
        return $this->getConnection()->executeQuery($sql, array($organizationId));
    }


}