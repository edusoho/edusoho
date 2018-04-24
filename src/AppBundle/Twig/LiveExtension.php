<?php

namespace AppBundle\Twig;

use Biz\CloudPlatform\Client\CloudAPIIOException;
use Biz\Util\EdusohoLiveClient;
use Codeages\Biz\Framework\Context\Biz;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LiveExtension extends \Twig_Extension
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var Biz
     */
    protected $biz;

    public function __construct(ContainerInterface $container, Biz $biz)
    {
        $this->container = $container;
        $this->biz = $biz;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('live_can_record', array($this, 'canRecord')),
            new \Twig_SimpleFunction('is_live_finished', array($this, 'isLiveFinished')),
            new \Twig_SimpleFunction('get_live_room_type', array($this, 'getLiveRoomType')),
        );
    }

    public function canRecord($liveId)
    {
        $client = new EdusohoLiveClient();

        try {
            return (bool) $client->isAvailableRecord($liveId);
        } catch (CloudAPIIOException $cloudAPIIOException) {
            return false;
        }
    }

    public function isLiveFinished($mediaId, $type)
    {
        if ($type == 'openCourse') {
            return $this->getLiveCourseService()->isLiveFinished($mediaId);
        } else {
            return $this->getActivityService()->isLiveFinished($mediaId);
        }
    }

    public function getLiveRoomType()
    {
        $setting = $this->getSettingService()->get('live-course', array());

        $roomTypes = empty($setting['room_type']) ? array() : $setting['room_type'];
        if (empty($roomTypes) || (time() - $setting['check_room_type_time']) >= 3600) {
            $roomTypes = $this->getRoomTypes();
            $setting['room_type'] = $roomTypes;
            $setting['check_room_type_time'] = time();
            $this->getSettingService()->set('live-course', $setting);
        }

        $default = array(
            'large' => 'course.live_activity.large_room_type',
            'small' => 'course.live_activity.small_room_type',
        );

        if (empty($roomTypes)) {
            return array();
        }

        if (count($roomTypes) >= 2) {
            return $default;
        } else {
            return array($roomTypes[0] => $default[$roomTypes[0]]);
        }
    }

    protected function getRoomTypes()
    {
        $client = new EdusohoLiveClient();
        try {
            $result = $client->getLiveAccount();

            return $result['roomType'];
        } catch (CloudAPIIOException $cloudAPIIOException) {
            return array();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'live';
    }

    protected function getActivityService()
    {
        return $this->biz->service('Activity:ActivityService');
    }

    protected function getLiveCourseService()
    {
        return $this->biz->service('OpenCourse:LiveCourseService');
    }

    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }
}
