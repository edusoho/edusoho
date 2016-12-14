<?php


namespace Biz;


use Biz\Common\HTMLHelper;
use Biz\File\FireWall\FireWallFactory;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class DefaultServiceProvider implements ServiceProviderInterface
{
    public function register(Container $biz)
    {
        $biz['html_helper'] = new HTMLHelper($biz);

        $biz['file_fire_wall_factory'] = function ($biz){
            return new FireWallFactory($biz);
        };
    }

}