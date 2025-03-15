<?php

namespace AgentBundle\Executor;

use Topxia\Service\Common\ServiceKernel;

// ServiceRegistry.php
class ServiceRegistry
{
    private static $services = [
        // EduSoho AI核心服务注册
        'PlanService' => 'StudyPlan:StudyPlanService',
    ];

    public static function resolve(string $serviceName): object
    {
        if (!isset(self::$services[$serviceName])) {
            return [
                'status' => '400002',
            ];
        }
        $serviceClass = self::$services[$serviceName];

        return ServiceKernel::instance()->getBiz()->service('AgentBundle:'.$serviceClass);
    }
}
