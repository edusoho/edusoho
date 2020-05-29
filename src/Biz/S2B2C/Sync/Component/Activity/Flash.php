<?php

namespace Biz\S2B2C\Sync\Component\Activity;

use Biz\Activity\Dao\FlashActivityDao;

class Flash extends Activity
{
    public function sync($activity, $config = [])
    {
        $newFlashFields = $this->getFlashActivityFields($activity, $config);

        return $this->getFlashActivityDao()->create($newFlashFields);
    }

    public function updateToLastedVersion($activity, $config = [])
    {
        $newFlashFields = $this->getFlashActivityFields($activity, $config);

        $existFlash = $this->getFlashActivityDao()->search(['syncId' => $newFlashFields['syncId']], [], 0, PHP_INT_MAX);
        if (!empty($existFlash)) {
            return $this->getFlashActivityDao()->update($existFlash[0]['id'], $newFlashFields);
        }

        return $this->getFlashActivityDao()->create($newFlashFields);
    }

    protected function getFlashActivityFields($activity, $config)
    {
        $user = $this->getCurrentUser();
        $flash = $activity[$activity['mediaType'].'Activity'];
        $newUploadFiles = $config['newUploadFiles'];

        return [
            'mediaId' => empty($newUploadFiles[$flash['mediaId']]) ? 0 : $newUploadFiles[$flash['mediaId']]['id'],
            'finishType' => $flash['finishType'],
            'finishDetail' => $flash['finishDetail'],
            'createdUserId' => $user['id'],
            'syncId' => $flash['id'],
        ];
    }

    /**
     * @return FlashActivityDao
     */
    protected function getFlashActivityDao()
    {
        return $this->getBiz()->dao('Activity:FlashActivityDao');
    }
}
