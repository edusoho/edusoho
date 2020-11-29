<?php

namespace Biz\Visualization\Service;

interface LearnControlService
{
    public function getUserLastLearnRecord($userId);

    public function getUserLastLearnRecordBySign($userId, $sign);

    public function getUserLatestActiveFlow($userId);

    public function freshFlow($userId, $sign);

    public function checkActive($userId, $sign, $reActive = false);

    public function checkCreateNewFlow($userId, $invalidSign = '');

    public function getMultipleLearnSetting();
}
