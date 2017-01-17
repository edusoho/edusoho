<?php
namespace Biz\Activity\Type;

use Biz\Activity\Dao\DownloadActivityDao;
use Biz\Activity\Dao\DownloadFileDao;
use Biz\Activity\Config\Activity;
use Biz\Activity\Service\ActivityLearnLogService;

class Download extends Activity
{
    protected function registerListeners()
    {
        // TODO: Implement registerListeners() method.
    }

    /**
     * @inheritdoc
     */
    public function create($fields)
    {
        $files   = json_decode($fields['materials'], true);
        $fileIds = array_keys($files);

        $downloadActivity = array('mediaCount' => count($files), 'fileIds' => $fileIds);
        $downloadActivity = $this->getDownloadActivityDao()->create($downloadActivity);

        return $downloadActivity;
    }

    /**
     * @inheritdoc
     */
    public function update($id, &$fields, $activity)
    {
        $files = json_decode($fields['materials'], true);

        $fileIds = array_keys($files);

        $downloadActivity = array('mediaCount' => count($files), 'fileIds' => $fileIds);
        $downloadActivity = $this->getDownloadActivityDao()->update($id, $downloadActivity);

        return $downloadActivity;

    }

    /**
     * @inheritdoc
     */
    public function delete($id)
    {
        return $this->getDownloadActivityDao()->delete($id);
    }

    public function isFinished($activityId)
    {
        $result = $this->getActivityLearnLogService()->findMyLearnLogsByActivityIdAndEvent($activityId, 'download.finish');
        return !empty($result);
    }

    /**
     * @inheritdoc
     */
    public function get($id)
    {
        return $this->getDownloadActivityDao()->get($id);
    }

    /**
     * @return DownloadActivityDao
     */
    public function getDownloadActivityDao()
    {
        return $this->getBiz()->dao('Activity:DownloadActivityDao');
    }

    /**
     * @return DownloadFileDao
     */
    public function getDownloadFileDao()
    {
        return $this->getBiz()->dao('Activity:DownloadFileDao');
    }

    /**
     * @return ActivityLearnLogService
     */
    protected function getActivityLearnLogService()
    {
        return $this->getBiz()->service("Activity:ActivityLearnLogService");
    }

    protected function getConnection()
    {
        return $this->getBiz()->offsetGet('db');
    }
}
