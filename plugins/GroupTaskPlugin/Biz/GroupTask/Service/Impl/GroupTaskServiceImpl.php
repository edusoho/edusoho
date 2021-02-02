<?php
namespace GroupTaskPlugin\Biz\GroupTask\Service\Impl;

/**
 * EduSoho系统可引用以下BaseService
 * Biz\BaseService
 */
use Codeages\Biz\Framework\Service\BaseService;
use GroupTaskPlugin\Biz\GroupTask\Service\GroupTaskService;

class GroupTaskServiceImpl extends BaseService implements GroupTaskService
{
    protected function getGroupTaskDao()
    {
        return $this->createDao('GroupTaskPlugin:GroupTask:GroupTaskDao');
    }
}