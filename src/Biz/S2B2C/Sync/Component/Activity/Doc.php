<?php

namespace Biz\S2B2C\Sync\Component\Activity;

use Biz\Activity\Dao\DocActivityDao;

class Doc extends Activity
{
    public function sync($activity, $config = [])
    {
        $newDoc = $this->getDocActivityFields($activity, $config);

        return $this->getDocActivityDao()->create($newDoc);
    }

    public function updateToLastedVersion($activity, $config = [])
    {
        $newDoc = $this->getDocActivityFields($activity, $config);

        $existDoc = $this->getDocActivityDao()->search(['syncId' => $newDoc['syncId']], [], 0, PHP_INT_MAX);
        if (!empty($existDoc)) {
            unset($newDoc['createdUserId']);

            return $this->getDocActivityDao()->update($existDoc[0]['id'], $newDoc);
        }

        return $this->getDocActivityDao()->create($newDoc);
    }

    protected function getDocActivityFields($activity, $config)
    {
        $user = $this->getCurrentUser();
        $doc = $activity[$activity['mediaType'].'Activity'];
        $newUploadFiles = $config['newUploadFiles'];

        return [
            'mediaId' => empty($newUploadFiles[$doc['mediaId']]) ? 0 : $newUploadFiles[$doc['mediaId']]['id'],
            'finishType' => $doc['finishType'],
            'finishDetail' => $doc['finishDetail'],
            'createdUserId' => $user['id'],
            'syncId' => $doc['id'],
        ];
    }

    /**
     * @return DocActivityDao
     */
    protected function getDocActivityDao()
    {
        return $this->getBiz()->dao('Activity:DocActivityDao');
    }
}
