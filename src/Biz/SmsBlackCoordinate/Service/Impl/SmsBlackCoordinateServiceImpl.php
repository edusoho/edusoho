<?php

namespace Biz\SmsBlackCoordinate\Service\Impl;

use Biz\BaseService;
use Biz\SmsBlackCoordinate\Dao\SmsBlackCoordinateDao;
use Biz\SmsBlackCoordinate\Service\SmsBlackCoordinateService;

class SmsBlackCoordinateServiceImpl extends BaseService implements SmsBlackCoordinateService
{

    public function isInBlackList($coordinate)
    {
        $existBlackList = $this->getSmsBlackListDao()->getByCoordinate($coordinate);
        if (empty($existBlackList)){
            $this->getSmsBlackListDao()->create(["hit_counts"=>1,"expire_time"=> time() + 24 * 3600, "coordinate" => $coordinate]);
            return false;
        }
        if ($existBlackList['expire_time'] < time()){
            $this->getSmsBlackListDao()->update($existBlackList['id'],["hit_counts"=>1,"expire_time"=> time() + 24 * 3600]);
            return false;
        }
        $this->getSmsBlackListDao()->update($existBlackList['id'], ["hit_counts"=>$existBlackList['hit_counts']+1]);
        if ($this->isInTop10AndTimeFilled($coordinate)){
            return true;
        }

        return false;
    }

    public function isInTop10AndTimeFilled($coordinate)
    {
        $blackCoordinates = $this->getSmsBlackListDao()->search([],['hit_counts' => 'DESC'], 0, 10);
        foreach ($blackCoordinates as $blackCoordinate) {
            if ($blackCoordinate['coordinate'] == $coordinate && $blackCoordinate['hit_counts'] >2){
                return true;
            }
        }
        return false;
    }

    /**
     * @return SmsBlackCoordinateDao
     */
    protected function getSmsBlackListDao()
    {
        return $this->createDao('SmsBlackCoordinate:SmsBlackCoordinateDao');
    }
}