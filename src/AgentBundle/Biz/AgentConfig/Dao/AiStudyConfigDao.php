<?php

namespace AgentBundle\Biz\AgentConfig\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface AiStudyConfigDao extends GeneralDaoInterface
{
    public function getByCourseId($courseId);

    public function findByCourseIds($courseIds);

    public function findByDomainId($domainId);

    public function findIndexing();
}
