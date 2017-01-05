<?php
namespace Biz\Activity\Type;

use Biz\Activity\Dao\DownloadFileDao;
use Topxia\Common\ArrayToolkit;
use Biz\Activity\Config\Activity;

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
        $materials = json_decode($fields['materials'], true);
        $that      = $this;
        $ext       = $this->getConnection()->transactional(function () use ($materials, $that) {
            //1. created ext
            $downloadActivity = array('mediaCount' => count($materials));
            $downloadActivity = $that->getDownloadActivityDao()->create($downloadActivity);
            //2. created file
            $files = $that->parseDownloadFiles($downloadActivity['id'], $materials);

            foreach ($files as $file) {
                $that->getDownloadFileDao()->create($file);
            }
            return $downloadActivity;
        });
        return $ext;
    }

    /**
     * @inheritdoc
     */
    public function update($id, &$fields, $activity)
    {
        $materials = json_decode($fields['materials'], true);

        $existMaterials = $this->getDownloadFileDao()->findByDownloadActivityId($id);
        $existMaterials = ArrayToolkit::index($existMaterials, 'indicate');

        $downloadActivity = $this->getDownloadActivityDao()->get($id);

        $that  = $this;
        $files = $this->parseDownloadFiles($id, $materials);

        $dropMaterials   = array_diff_key($existMaterials, $files);
        $addMaterials    = array_diff_key($files, $existMaterials);
        $updateMaterials = array_intersect_key($existMaterials, $files);

        $this->getConnection()->transactional(function () use ($id, $dropMaterials, $addMaterials, $updateMaterials, $that) {
            foreach ($dropMaterials as $material) {
                $that->getDownloadFileDao()->delete($material['id']);
            }

            foreach ($addMaterials as $material) {
                $that->getDownloadFileDao()->create($material);
            }

            foreach ($updateMaterials as $material) {
                $that->getDownloadFileDao()->update($material['id'], $material);
            }
        });
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
        $result = $this->getActivityLearnLogService()->findMyLearnLogsByActivityIdAndEvent($activityId, 'download.download');
        return !empty($result);
    }

    protected function getListeners()
    {
        // TODO: Implement getListeners() method.
    }

    /**
     * @inheritdoc
     */
    public function get($id)
    {
        $downloadActivity              = $this->getDownloadActivityDao()->get($id);
        $downloadActivity['materials'] = $this->getDownloadFileDao()->findByDownloadActivityId($downloadActivity['id']);
        return $downloadActivity;
    }

    public function parseDownloadFiles($downloadActivityId, $materials)
    {
        $files = array();
        array_walk($materials, function ($material) use ($downloadActivityId, &$files) {
            $file = array(
                'downloadActivityId' => $downloadActivityId,
                'title'              => $material['name'],
                'fileId'             => intval($material['id']),
                //FIXME undefined index: size
                // 'fileSize'           => $material['size'],
                'indicate'           => intval($material['id']),
                'summary'            => empty($material['summary']) ? null : $material['summary']
            );
            if (intval($material['id']) == 0) {
                $file['link']     = $material['link'];
                $file['indicate'] = $file['link'];
            }
            $files[$file['indicate']] = $file;
        });
        return $files;
    }

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

    protected function getActivityLearnLogService()
    {
        return $$this->getBiz()->service("Activity:ActivityLearnLogService");
    }

    protected function getConnection()
    {
        return $this->getBiz()->offsetGet('db');
    }
}
