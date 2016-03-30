<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;

class MeChatroomes extends BaseResource
{
    public function get(Application $app, Request $request)
    {
        $start = $request->query->get('start', 0);
        $limit = $request->query->get('limit', 10);

        $user = $this->getCurrentUser();

        $conditions = array('userId' => $user['id']);
        $total = $this->getClassroomService()->searchMemberCount($conditions);
        $members = $this->getClassroomService()->searchMembers($conditions, array('createdTime', 'DESC'), $start, $limit);

        $classroomIds = ArrayToolkit::column($members, 'classroomId');

        $classrooms = $this->getClassroomService()->findClassroomsByIds($classroomIds);

        $chatrooms = array();
        foreach ($classrooms as $classroom) {
            $chatrooms[] = array(
                'type' => 'classroom',
                'id' => $classroom['id'],
                'title' => $classroom['title'],
                'picture' => $this->getFileUrl($classroom['smallPicture']),
            );
        }

        return $this->wrap($this->filter($chatrooms), $total);
    }

    public function filter($res)
    {
        return $res;
    }

    protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }
}
