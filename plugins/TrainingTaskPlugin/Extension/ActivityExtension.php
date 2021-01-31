<?php

namespace TrainingTaskPlugin\Extension;

use AppBundle\Extension\Extension;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use TrainingTaskPlugin\Biz\Activity\Type\Training;

class ActivityExtension extends Extension implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $container)
    {
        $container['activity_type.training'] = function ($biz) {
            return new Training($biz);
        };
    }

    public function getActivities()
    {
        $biz = $this->biz;

        return array(
            'training' => array(
                'meta' => array(
                    'name' => '实训',                              //任务类型名称
                    'icon' => 'es-icon es-icon-graphicclass',     //任务类型图片
                ),
                'typeClass' => '\Biz\Activity\Type\Training',
                'controller' => 'TrainingTaskPlugin:Activity/Training', //任务控制器
                'visible' => function ($courseSet, $course) {        //是否可见
                    return true;
                },
            )
        );
    }
}