<?php

namespace AgentBundle\Biz\StudyPlan\Dao;

interface AiStudyConfigDao
{
    public function getAiStudyConfigByCourseId($courseId);
}
