<?php

namespace AgentBundle\Biz\AgentConfig\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface AiStudyConfigDao extends GeneralDaoInterface
{
    public function getAiStudyConfigByCourseId($courseId);
}
