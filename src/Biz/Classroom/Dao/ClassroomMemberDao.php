<?php


namespace Biz\Classroom\Dao;


use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface ClassroomMemberDao extends GeneralDaoInterface
{
    public function findByUserIdAndClassroomIds($userId, $classroomIds);
}