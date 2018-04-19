<?php

namespace AppBundle\Twig;

use Biz\CloudPlatform\Client\CloudAPIIOException;
use Biz\Util\EdusohoLiveClient;

class LiveExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('live_can_record', array($this, 'canRecord')),
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

    public function getLiveRoomType()
    {
        $default = array(
            'large' => 'course.live_activity.large_room_type',
            'small' => 'course.live_activity.small_room_type'
        );

        /*$client = new EdusohoLiveClient();
        try {
            $result = $client->getLiveAccount();

            $roomTypes = $result['roomType'];
            if (count($roomTypes) >= 2) {
                return $default;
            } else {
                return array($roomTypes[0] => $default[$roomTypes[0]]);
            }
        } catch (CloudAPIIOException $cloudAPIIOException) {
            return array($roomTypes[0] => $roomTypes[$result[0]]);
        }*/

        return $default;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'live';
    }
}
