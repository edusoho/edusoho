<?php
namespace WebBundle\Twig;

use Biz\Activity\Config\ActivityFactory;

class ActivityExtension extends \Twig_Extension
{
    protected $biz;

    public function __construct($biz)
    {
        $this->biz = $biz;
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('activity_length_format', array($this, 'lengthFormat')),
        );
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('activity_metas', array($this, 'getActivityMetas')),
        );
    }

    public function lengthFormat($len)
    {
        if (empty($len) || $len == 0) {
            return null;
        }
        $h = floor($len / 60);
        $m = fmod($len, 60);
        //TODO 目前没考虑秒
        return ($h < 10 ? '0'.$h : $h).':'.($m < 10 ? '0'.$m : $m).':00';
    }

    public function getActivityMetas($type)
    {
        $metas = ActivityFactory::all($this->biz);
        return $metas[$type]->getMetas();
    }

    public function getName()
    {
        return 'web_activity_twig';
    }
}