<?php

namespace AppBundle\Extension;

use Biz\Favorite\Types\Course;
use Biz\Favorite\Types\Goods;
use Biz\Favorite\Types\OpenCourse;
use Deployer\Exception\Exception;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class FavoriteTypesExtension extends Exception implements ServiceProviderInterface
{
    public function getFavoriteTypes()
    {
        return [
            'course' => [
                'class' => Course::class,
            ],
            'goods' => [
                'class' => Goods::class,
            ],
            'openCourse' => [
                'class' => OpenCourse::class,
            ],
        ];
    }

    public function register(Container $container)
    {
        $container['favorite_types.course'] = function ($biz) {
        };
    }
}
