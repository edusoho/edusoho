<?php


namespace Custom\Service\CrontabJob\Dao\Impl;


use Custom\Service\CrontabJob\Dao\CrontabJobDao;
use Topxia\Service\Crontab\Dao\Impl\JobDaoImpl as BaseDaoImpl;

class CrontabJobDaoImpl extends BaseDaoImpl implements CrontabJobDao
{
    private $serializeFields = array(
        'jobParams' => 'json',
    );

    public function getJobByNameAndTargetTypeAndTargetId($jobName, $targetType, $targetId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE name= ? AND targetType = ? AND targetId = ?";
        $job = $this->getConnection()->fetchAssoc($sql, array($jobName, $targetType, $targetId)) ? : array();
        return $this->createSerializer()->unserialize($job, $this->serializeFields);
    }
}