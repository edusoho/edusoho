<?php

namespace Codeages\Biz\Framework\Scheduler\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Codeages\Biz\Framework\Scheduler\Dao\JobFiredDao;

class JobFiredDaoImpl extends GeneralDaoImpl implements JobFiredDao
{
    protected $table = 'biz_scheduler_job_fired';

    public function getByStatus($status)
    {
        $sql = "SELECT * FROM {$this->table} 
                  WHERE fired_time <= ? AND status = ? 
                ORDER BY priority DESC , fired_time ASC LIMIT 1";

        return $this->db()->fetchAssoc($sql, array(strtotime('+1 minutes'), $status));
    }

    public function findByJobId($jobId)
    {
        return $this->findByFields(array(
            'job_id' => $jobId,
        ));
    }

    public function findByJobIdAndStatus($jobId, $status)
    {
        return $this->findByFields(array(
            'job_id' => $jobId,
            'status' => $status,
        ));
    }

    public function deleteUnacquiredBeforeCreatedTime($time)
    {
        $sql = "delete FROM {$this->table} where created_time < ? and status != ?";

        return $this->db()->executeUpdate($sql, array($time, 'acquired'));
    }

    public function declares()
    {
        return array(
            'timestamps' => array('created_time', 'updated_time'),
            'orderbys' => array('created_time', 'id'),
            'serializes' => array(
                'job_detail' => 'json',
            ),
            'conditions' => array(
                'job_id = :job_id',
                'status = :status',
                'fired_time < :fired_time_LT',
                'job_name = :job_name',
            ),
        );
    }
}
