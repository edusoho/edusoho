<?php

namespace CustomBundle\Extension;

use CustomBundle\Biz\Activity\Type\Demo;
use Pimple\Container;
use AppBundle\Extension\ActivityExtension as BaseExtension;

class ActivityExtension extends BaseExtension
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $container)
    {
        // type defined in parent  can be rewrite
        parent::register($container);

//        $container['activity_type.demo'] = function ($biz) {
//            return new Demo($biz);
//        };
    }

    public function getActivities()
    {
        // activity define in parent can be unset or rewrite
        $activities = parent::getActivities();

//        $activities['video'] = array(
//        //$activities['demo'] = array(
//            'meta' => array(
//                'name' => '在线编程',
//                'icon' => 'es-icon es-icon-filedownload',
//            ),
//            'controller' => 'CustomBundle:Activity/Demo',
//            'visible' => function ($courseSet, $course) {
//                return true;
//            },
//        );

        return $activities;
    }
}
