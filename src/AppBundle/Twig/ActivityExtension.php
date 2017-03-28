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
        );
    }

    public function getActivityMeta($type = null)
    {
        $activities = $this->container->get('extension.manager')->getActivities();

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

    public function lengthFormat($len)
    {
        if (empty($len) || $len == 0) {
            return null;
        }
        $h = floor($len / 60);
        $m = fmod($len, 60);

        return ($h < 10 ? '0'.$h : $h).':'.($m < 10 ? '0'.$m : $m);
    }

    public function getName()
    {
        return 'web_activity_twig';
    }
}
