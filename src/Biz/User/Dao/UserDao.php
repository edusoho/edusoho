<?php

namespace Biz\User\Dao;

interface UserDao
{
    public function getByEmail($email);

    public function getByNickname($nickname);

    public function countByMobileNotEmpty();

    public function getByVerifiedMobile($mobile);

    public function findByNicknames(array $nicknames);

    public function findByIds(array $ids);

    public function getByInviteCode($code);

    public function waveCounterById($id, $name, $number);

    public function clearCounterById($id, $name);

    public function analysisRegisterDataByTime($startTime, $endTime);

    public function analysisUserSumByTime($endTime);
}
