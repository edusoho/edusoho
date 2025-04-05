<?php

namespace AgentBundle\Biz\AgentConfig\Dao\Impl;

use AgentBundle\Biz\AgentConfig\Dao\AiStudyConfigDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class AiStudyConfigDaoImpl extends GeneralDaoImpl implements AiStudyConfigDao
{
    protected $table = 'ai_study_config';

    public function getByCourseId($courseId)
    {
        return $this->getByFields(['courseId' => $courseId]);
    }

    public function findByCourseIds($courseIds)
    {
        return $this->findInField('courseId', $courseIds);
    }

    public function findByDomainId($domainId)
    {
        return $this->findByFields(['domainId' => $domainId]);
    }

    public function findIndexing()
    {
        return $this->findByFields(['indexing' => 1]);
    }

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'serializes' => ['planDeadline' => 'json'],
        ];
    }
}
