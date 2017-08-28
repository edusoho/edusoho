<?php

namespace Codeages\Biz\Framework\Targetlog\Service\Impl;

use Codeages\Biz\Framework\Service\BaseService;
use Codeages\Biz\Framework\Targetlog\Service\TargetlogService;

class TargetlogServiceImpl extends BaseService implements TargetlogService
{
    public function log($level, $targetType, $targetId, $message, array $context = array())
    {
        $log = array();

        $log['level'] = $level;
        $log['target_type'] = $targetType;
        $log['target_id'] = $targetId;
        $log['message'] = $message;
        $log['action'] = isset($context['@action']) ? $context['@action'] : '';
        $log['user_id'] = isset($context['@user_id']) ? $context['@user_id'] : 0;
        $log['ip'] = isset($context['@ip']) ? $context['@ip'] : '';

        unset($context['@action']);
        unset($context['@user_id']);
        unset($context['@ip']);

        $log['context'] = empty($context) ? array() : $context;

        return $this->getLogDao()->create($log);
    }

    public function getLog($id)
    {
        return $this->getLogDao()->get($id);
    }

    public function searchLogs($conditions, $orderBy, $start, $limit)
    {
        return $this->getLogDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function countLogs($conditions)
    {
        return $this->getLogDao()->count($conditions);
    }

    protected function getLogDao()
    {
        return $this->biz->dao('Targetlog:TargetlogDao');
    }
}
