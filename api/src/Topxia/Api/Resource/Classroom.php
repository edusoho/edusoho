<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class Classroom extends BaseResource
{
    public function get(Application $app, Request $request, $id)
    {
        $simplify = (boolean) $request->query->get('simplify');

        $classroom = $this->getClassroomService()->getClassroom($id);
        if (empty($classroom)) {
            return $this->error(500, "ID为{$id}的教室不存在");
        }

        if ($simplify) {
            return $this->simplify($classroom);
        }
        return $this->filter($classroom);
    }

    public function post(Application $app, Request $request, $id)
    {
    }

    protected function simplify($res)
    {
        $simple = array();

        $simple['id']            = $res['id'];
        $simple['picture']       = $res['middlePicture'];
        $simple['title']         = $res['title'];
        $simple['about']         = $res['about'];
        $simple['headTeacherId'] = $res['headTeacherId'];
        $simple['teacherIds']    = $res['teacherIds'];
        $simple['convNo']        = $this->getConversation($res['id']);

        return $simple;
    }

    public function filter($res)
    {
        $res['createdTime'] = date('c', $res['createdTime']);
        $res['convNo']      = $this->getConversation($res['id']);
        $res['about']       = is_null($res['about']) ? '' : $res['about'];
        foreach (array('smallPicture', 'middlePicture', 'largePicture') as $key) {
            $res[$key] = $this->getFileUrl($res[$key]);
        }

        return $res;
    }

    protected function getConversation($classroomId)
    {
        $conversation = $this->getConversationService()->getConversationByTarget($classroomId, 'classroom');

        if ($conversation) {
            return $conversation['no'];
        }

        return '';
    }

    protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:ClassroomService');
    }

    protected function getConversationService()
    {
        return $this->getServiceKernel()->createService('IM:ConversationService');
    }
}
