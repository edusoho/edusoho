<?php

namespace Biz\BehaviorVerification\Service\Impl;

use AppBundle\Common\EncryptionToolkit;
use Biz\BaseService;
use Biz\BehaviorVerification\Dao\BehaviorVerificationCoordinateDao;
use Biz\BehaviorVerification\Service\BehaviorVerificationCoordinateService;

class BehaviorVerificationCoordinateServiceImpl extends BaseService implements BehaviorVerificationCoordinateService
{

    public function isRobot($coordinate)
    {
        $existCoordinate = $this->getBehaviorVerificationCoordinateDao()->getByCoordinate($coordinate);
        if (empty($existCoordinate)) {
            $this->getBehaviorVerificationCoordinateDao()->create(["hit_counts" => 1, "expire_time" => time() + 24 * 3600, "coordinate" => $coordinate]);
            return false;
        }
        if ($existCoordinate['expire_time'] < time()) {
            $this->getBehaviorVerificationCoordinateDao()->update($existCoordinate['id'], ["hit_counts" => 1, "expire_time" => time() + 24 * 3600]);
            return false;
        }
        $this->getBehaviorVerificationCoordinateDao()->wave([$existCoordinate['id']], ['hit_counts' => +1,]);
        if ($this->isInTop10AndTimeFilled($coordinate)) {
            return true;
        }

        return false;
    }

    public function isInTop10AndTimeFilled($coordinate)
    {
        $blackCoordinates = $this->getBehaviorVerificationCoordinateDao()->search([], ['hit_counts' => 'DESC'], 0, 10);
        foreach ($blackCoordinates as $blackCoordinate) {
            if ($blackCoordinate['coordinate'] == $coordinate && $blackCoordinate['hit_counts'] > 2) {
                return true;
            }
        }
        return false;
    }

    public function decryptCoordinate($coordinate)
    {
        global $kernel;
        $csrfToken = $kernel->getContainer()->get('security.csrf.token_manager')->getToken('site');
        return EncryptionToolkit::XXTEADecrypt(base64_decode(mb_substr($coordinate, 2)), $csrfToken);
    }

    /**
     * @return BehaviorVerificationCoordinateDao
     */
    protected function getBehaviorVerificationCoordinateDao()
    {
        return $this->createDao('BehaviorVerification:BehaviorVerificationCoordinateDao');
    }
}