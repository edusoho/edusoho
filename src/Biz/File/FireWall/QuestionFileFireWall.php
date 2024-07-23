<?php

namespace Biz\File\FireWall;

class QuestionFileFireWall extends BaseFireWall implements FireWallInterface
{
    public function canAccess($attachment)
    {
        $user = $this->getCurrentUser();
        if ($user->isAdmin()) {
            return true;
        }

        return false;
    }
}
