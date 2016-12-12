<?php
/**
 * Created by PhpStorm.
 * User: Simon
 * Date: 09/12/2016
 * Time: 16:40
 */

namespace AppBundle\Twig;

class ActivityExtension extends \Twig_Extension
{
    protected $biz;
    protected $container;

    public function __construct($container, $biz)
    {
        $this->container = $container;
        $this->biz       = $biz;
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
            new \Twig_SimpleFunction('activity_meta', array($this, 'getActivityMeta')),
            new \Twig_SimpleFunction('activity_metas', array($this, 'getActivityMeta'))
        );
    }

    public function getActivityMeta($type = null)
    {
        $activities = $this->container->get('extension.default')->getActivities();

        if (empty($type)) {
            $activities = array_map(function ($activity) {
                return $activity['meta'];
            },$activities);
            return $activities;
        } else {
            if (isset($activities[$type]) && isset($activities[$type]['meta'])) {
                return $activities[$type]['meta'];
            }
            return null;
        }
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

    public function getName()
    {
        return 'web_activity_twig';
    }
}