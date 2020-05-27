<?php

namespace Biz\S2B2C\Sync\Component\Activity;

use Biz\Activity\Dao\TextActivityDao;

class Text extends Activity
{
    public function sync($activity, $config = [])
    {
        $newTextFields = $this->getTextActivityFields($activity, $config);

        return $this->getTextActivityDao()->create($newTextFields);
    }

    public function updateToLastedVersion($activity, $config = [])
    {
        $newTextFields = $this->getTextActivityFields($activity, $config);

        $existText = $this->getTextActivityDao()->search(['syncId' => $newTextFields['syncId']], [], 0, PHP_INT_MAX);
        if (!empty($existText)) {
            unset($existText['createdUserId']);

            return $this->getTextActivityDao()->update($existText[0]['id'], $newTextFields);
        }

        return $this->getTextActivityDao()->create($newTextFields);
    }

    protected function getTextActivityFields($activity, $config)
    {
        $user = $this->getCurrentUser();
        $text = $activity[$activity['mediaType'].'Activity'];

        return [
            'syncId' => $text['id'],
            'finishType' => $text['finishType'],
            'finishDetail' => $text['finishDetail'],
            'createdUserId' => $user['id'],
        ];
    }

    /**
     * @return TextActivityDao
     */
    protected function getTextActivityDao()
    {
        return $this->getBiz()->dao('Activity:TextActivityDao');
    }
}
