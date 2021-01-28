<?php

namespace Biz\File\FireWall;

interface FireWallInterface
{
    /**
     * @param $attachment
     *
     * @return bool
     */
    public function canAccess($attachment);
}
