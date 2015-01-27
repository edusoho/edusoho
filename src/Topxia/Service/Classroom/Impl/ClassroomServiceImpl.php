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

    private function getClassroomDao() 
    {
        return $this->createDao('Classroom.ClassroomDao');
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

    public function updateClassroom($id,$fields)
    {   
        if (empty(trim($fields['title']))) {
            throw $this->createServiceException("班级名称不能为空！");
        }

        $classroom['title']=$fields['title'];
        $classroom['about']=$fields['about'];
        $classroom['description']=$fields['description'];

        $classroom=$this->getClassroomDao()->updateClassroom($id,$classroom);

        return $classroom;
    }
}
