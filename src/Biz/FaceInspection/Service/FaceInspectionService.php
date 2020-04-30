<?php

namespace Biz\FaceInspection\Service;

interface FaceInspectionService
{
    public function createUserFace($fields);

    public function updateUserFace($id, $fields);

    public function getUserFaceByUserId($userId);

    public function countUserFaces($conditions);

    public function searchUserFaces($conditions, $orderBys, $start, $limit);

    public function searchUsersJoinUserFace($conditions, $start, $limit);

    public function countUsersJoinUserFace($conditions);
}
