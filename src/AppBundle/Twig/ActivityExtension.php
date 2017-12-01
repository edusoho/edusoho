<?php

namespace AppBundle\Twig;

use Codeages\Biz\Framework\Context\Biz;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ActivityExtension extends \Twig_Extension
{
    /**
     * @var Biz
     */
    protected $biz;

    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container, Biz $biz)
    {
        $this->container = $container;
        $this->biz = $biz;
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('activity_length_format', array($this, 'lengthFormat')),
            new \Twig_SimpleFilter('activity_visible', array($this, 'isActivityVisible')),
        );
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('activity_meta', array($this, 'getActivityMeta')),
            new \Twig_SimpleFunction('activity_metas', array($this, 'getActivityMeta')),
            new \Twig_SimpleFunction('can_free_activity_types', array($this, 'getCanFreeActivityTypes')),
        );
    }

    public function getActivityMeta($type = null)
    {
        $activities = $this->container->get('extension.manager')->getActivities();

        foreach ($activities as &$activity) {
            $activity['meta']['name'] = $this->container->get('translator')->trans($activity['meta']['name']);
        }

        if (empty($type)) {
            $activities = array_map(function ($activity) {
                return $activity['meta'];
            }, $activities);

            return $activities;
        } else {
            if (isset($activities[$type]) && isset($activities[$type]['meta'])) {
                return $activities[$type]['meta'];
            }

            return array(
                'icon' => '',
                'name' => '',
            );
        }
    }

    /**
     * @param $type
     * @param $courseSet
     * @param $course
     *
     * @return bool
     */
    public function isActivityVisible($type, $courseSet, $course)
    {
        $activities = $this->container->get('extension.manager')->getActivities();

        return call_user_func($activities[$type]['visible'], $courseSet, $course);
    }

    public function lengthFormat($len, $type = null)
    {
        if (empty($len) || 0 == $len) {
            return null;
        }

        if (in_array($type, array('testpaper', 'live'))) {
            $len *= 60;
        }
        $h = floor($len / 3600);
        $m = fmod(floor($len / 60), 60);
        $s = fmod($len, 60);

        return $h > 0 ? (($h < 10 ? '0'.$h : $h).':'.($m < 10 ? '0'.$m : $m).':'.($s < 10 ? '0'.$s : $s)) : (($m < 10 ? '0'.$m : $m).':'.($s < 10 ? '0'.$s : $s));
    }

    public function getName()
    {
        return 'web_activity_twig';
    }

    public function getCanFreeActivityTypes()
    {
        $types = array();
        $activities = $this->container->get('extension.manager')->getActivities();
        foreach ($activities as $type => $activity) {
            if (isset($activity['canFree']) && $activity['canFree']) {
                $types[$type] = $this->container->get('translator')->trans($activity['meta']['name']);
            }
        }

        return $types;
    }
}
