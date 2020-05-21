<?php

namespace Biz\User\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface UserDao extends GeneralDaoInterface
{
    public function getByEmail($email);

    public function getUserByType($type);

    public function getByNickname($nickname);

    public function getUnDestroyedUserByNickname($nickname);

    public function getByUUID($uuid);

    public function countByMobileNotEmpty();

    public function findUnlockedUsersWithMobile($start, $limit);

    public function getByVerifiedMobile($mobile);

    public function countByLessThanCreatedTime($time);

    public function findUnDestroyedUsersByIds($ids);

    public function findByNicknames(array $nicknames);

    public function findByIds(array $ids);

    public function getByInviteCode($code);

    public function waveCounterById($id, $name, $number);

    public function deleteCounterById($id, $name);

    public function analysisRegisterDataByTime($startTime, $endTime);

    public function searchUsersJoinUserFace($conditions, $start, $limit);

    public function countUsersJoinUserFace($conditions);
}
