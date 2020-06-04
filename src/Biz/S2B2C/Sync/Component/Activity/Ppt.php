<?php

namespace Biz\S2B2C\Sync\Component\Activity;

use Biz\Activity\Dao\PptActivityDao;

class Ppt extends Activity
{
    public function sync($activity, $config = [])
    {
        $newPptFields = $this->getPptActivityFields($activity, $config);

        return $this->getPptActivityDao()->create($newPptFields);
    }

    public function updateToLastedVersion($activity, $config = [])
    {
        $newPptFields = $this->getPptActivityFields($activity, $config);

        $existPpt = $this->getPptActivityDao()->search(['syncId' => $newPptFields['syncId']], [], 0, PHP_INT_MAX);
        if (!empty($existPpt)) {
            unset($existPpt['createdUserId']);

            return $this->getPptActivityDao()->update($existPpt[0]['id'], $newPptFields);
        }

        return $this->getPptActivityDao()->create($newPptFields);
    }

    protected function getPptActivityFields($activity, $config)
    {
        $user = $this->getCurrentUser();
        $ppt = $activity[$activity['mediaType'].'Activity'];
        $newUploadFiles = $config['newUploadFiles'];

        return [
            'syncId' => $ppt['id'],
            'mediaId' => empty($newUploadFiles[$ppt['mediaId']]) ? 0 : $newUploadFiles[$ppt['mediaId']]['id'],
            'finishType' => $ppt['finishType'],
            'finishDetail' => $ppt['finishDetail'],
            'createdUserId' => $user['id'],
        ];
    }

    /**
     * @return PptActivityDao
     */
    protected function getPptActivityDao()
    {
        return $this->getBiz()->dao('Activity:PptActivityDao');
    }
}
