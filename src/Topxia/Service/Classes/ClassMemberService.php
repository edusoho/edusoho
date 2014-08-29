<?php
namespace Topxia\Service\Classes;

interface ClassMemberService
{
    public function searchClassMembers(array $conditions, array $orderBy, $start, $limit);

    public function searchClassMemberCount(array $conditions);

    public function addClassMember(array $classMember);

    public function updateClassMember(array $fields, $id);

    public function deleteClassMemberByUserId($userId);
}