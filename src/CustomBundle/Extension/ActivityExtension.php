<?php

namespace CustomBundle\Extension;

use AppBundle\Extension\Extension;
use CustomBundle\Biz\Activity\Type\Demo;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ActivityExtension extends Extension  implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $container)
    {
            //type defined in parent  can be rewrite
//            $container['activity_type.demo'] = function ($biz) {
//                return new Demo($biz);
//            };
    }

    public function getActivities()
    {
        // activity define in parent can be unset or rewrite
        $activities = array();
//        $activities['video'] = array(
//            'meta' => array(
//                'name' => '在线编程',
//                'icon' => 'es-icon es-icon-filedownload',
//            ),
//            'controller' => 'CustomBundle:Activity/Demo',
//            'visible' => function ($courseSet, $course) {
//                return true;
//            },
 //       );

        return $activities;
    }
}
