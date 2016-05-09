<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;

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

        $simple['id'] = $res['id'];
        $simple['picture'] = $res['middlePicture'];
        $simple['title'] = $res['title'];
        $simple['about'] = $res['about'];
        $simple['headTeacherId'] = $res['headTeacherId'];
        $simple['teacherIds'] = $res['teacherIds'];
        $simple['conversationNo'] = $res['conversationId'];

        return $simple;
    }

    public function filter($res)
    {
        foreach (array('createdTime', 'updatedTime') as $key) {
            if (isset($res[$key])) {
                $res[$key] = date('c', $res[$key]);
            }
        }

        foreach (array('smallPicture', 'middlePicture', 'largePicture') as $key) {
            $res[$key] = $this->getFileUrl($res[$key]);
        }

        return $res;
    }

    protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }
}
