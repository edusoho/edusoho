<?php

namespace Biz\File\FireWall;

class ClassroomFileFireWall extends BaseFireWall implements FireWallInterface
{
    public function canAccess($attachment)
    {
        $user = $this->getCurrentUser();
        if ($user->isAdmin()) {
            return true;
        }

        $targetTypes = explode('.', $attachment['targetType']);
        $type = array_pop($targetTypes);
        if ('thread' === $type) {
            $thread = $this->getThreadService()->getThread($attachment['targetId']);

            if ($user['id'] == $thread['userId']) {
                return true;
            }
            $classroom = $this->getClassroomService()->getClassroom($thread['targetId']);

            if (in_array($user['id'], $classroom['teacherIds']) || $user['id'] == $classroom['headTeacherId']) {
                return true;
            }
        } elseif ('post' === $type) {
            $post = $this->getThreadService()->getPost($attachment['targetId']);
            if ($user['id'] == $post['userId']) {
                return true;
            }
            $thread = $this->getThreadService()->getThread($post['threadId']);
            if ($user['id'] == $thread['userId']) {
                return true;
            }
            $classroom = $this->getClassroomService()->getClassroom($thread['targetId']);
            if (in_array($user['id'], $classroom['teacherIds'])) {
                return true;
            }
        }

        return false;
    }

    protected function getThreadService()
    {
        return $this->biz->service('Thread:ThreadService');
    }

    protected function getClassroomService()
    {
        return $this->biz->service('Classroom:ClassroomService');
    }
}
