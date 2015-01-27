<?php

namespace Topxia\Service\Classroom\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Classroom\ClassroomService;
use Topxia\Common\ArrayToolkit;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Image\ImageInterface;
use Symfony\Component\HttpFoundation\File\File;


class ClassroomServiceImpl extends BaseService implements ClassroomService 
{
    public function getClassroom($id)
    {
        return $this->getClassroomDao()->getClassroom($id);
    }

    public function searchClassrooms($conditions, $orderBy, $start, $limit)
    {
        return $this->getClassroomDao()->searchClassrooms($conditions,$orderBy,$start,$limit);
    }

    public function searchClassroomsCount($conditions)
    {
         $count= $this->getClassroomDao()->searchClassroomsCount($conditions);
         return $count;
    }

    public function addClassroom($classroom)
    {   
        $title=trim($classroom['title']);
        if (empty($title)) {
            throw $this->createServiceException("班级名称不能为空！");
        }

        $classroom['createdTime']=time();
        $classroom = $this->getClassroomDao()->addClassroom($classroom);

        return $classroom;
    }

    public function canTakeClassroom($classroom)
    {
        $classroom = !is_array($classroom) ? $this->getClassroom(intval($classroom)) : $classroom;
        if (empty($classroom)) {
            return false;
        }

        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            return false;
        }

        if (count(array_intersect($user['roles'], array('ROLE_ADMIN', 'ROLE_SUPER_ADMIN'))) > 0) {
            return true;
        }

        $member = $this->getMemberDao()->getMemberByClassIdAndUserId($classroom['id'], $user['id']);
        if ($member and in_array($member['role'], array('teacher', 'student','aduitor'))) {
            return true;
        }

        return false;
    }

    public function tryTakeClassroom($classId)
    {
        $classroom = $this->getClassroom($classId);
        if (empty($classroom)) {
            throw $this->createNotFoundException();
        }
        if ($classroom['status'] != 'published') {
            throw $this->createAccessDeniedException('班级未发布,无法查看,请联系管理员！');
        }

        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException('您尚未登录用户，请登录后再查看！');
        }

        $member = $this->getMemberDao()->getMemberByClassIdAndUserId($classId, $user['id']);
        if (count(array_intersect($user['roles'], array('ROLE_ADMIN', 'ROLE_SUPER_ADMIN'))) > 0) {
            return array($classroom, $member);
        }

        if (empty($member) or !in_array($member['role'], array('teacher', 'student','aduitor'))) {
            throw $this->createAccessDeniedException('您不是班级学员，不能查看课程内容，请先购买班级！');
        }

        return array($classroom, $member);
    }


    public function canManageClassroom($targetId)
    {
        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            return false;
        }
        if ($user->isAdmin()) {
            return true;
        }

        $classroom = $this->getClassroom($targetId);
        if (empty($classroom)) {
            return $user->isAdmin();
        }

        $member = $this->getMemberDao()->getMemberByClassIdAndUserId($targetId, $user->id);
        if ($member and ($member['role'] == 'teacher')) {
            return true;
        }

        return false;
    }

    public function getClassroomMember($classId, $userId)
    {
        return $this->getMemberDao()->getMemberByClassIdAndUserId($classId, $userId);
    }

    private function getClassroomDao() 
    {
        return $this->createDao('Classroom.ClassroomDao');
    }

    private function getMemberDao ()
    {
        return $this->createDao('Classroom.ClassroomMemberDao');
    }

    

}
