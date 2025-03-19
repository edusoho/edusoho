<?php

namespace AgentBundle\Biz\AgentConfig\Dao\Impl;

use AgentBundle\Biz\AgentConfig\Dao\AiStudyConfigDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class AiStudyConfigDaoImpl extends GeneralDaoImpl implements AiStudyConfigDao
{
    protected $table = 'ai_study_config';

    public function getAiStudyConfigByCourseId($courseId)
    {
        return $this->getByFields(['courseId' => $courseId]);
    }

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'serializes' => ['planDeadline' => 'json'],
        ];
    }
}
