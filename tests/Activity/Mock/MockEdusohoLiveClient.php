<?php

namespace Tests\Activity\Mock;

/*
Mock of Topxia\Service\Util\EdusohoLiveClient
 */
class MockEdusohoLiveClient
{
    public function __contruct()
    {
    }

    public function createLive($live)
    {
        return array(
            'id'       => rand(1, 1000),
            'provider' => rand(1, 10)
        );
    }

    public function updateLive($live)
    {
        return $live;
    }

    public function deleteLive($id, $provider)
    {
        return array(
            'id'       => $id,
            'provider' => $provider
        );
    }
}
