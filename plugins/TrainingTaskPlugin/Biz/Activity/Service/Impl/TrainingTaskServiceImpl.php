<?php
namespace TrainingTaskPlugin\Biz\TrainingTask\Service\Impl;

/**
 * EduSoho系统可引用以下BaseService
 * Biz\BaseService
 */
use Codeages\Biz\Framework\Service\BaseService;
use TrainingTaskPlugin\Biz\TrainingTask\Service\TrainingTaskService;

class TrainingTaskServiceImpl extends BaseService implements TrainingTaskService
{
    protected function getTrainingTaskDao()
    {
        return $this->createDao('TrainingTaskPlugin:TrainingTask:TrainingTaskDao');
    }
}