<?php

namespace Biz\S2B2C\Sync\Component\Activity;

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Dao\DownloadActivityDao;
use Biz\Course\Dao\CourseMaterialDao;

class Download extends Activity
{
    public function sync($activity, $config = array())
    {
        $download = $activity[$activity['mediaType'].'Activity'];
        $newUploadFiles = $config['newUploadFiles'];
        $newDownloadFields = $this->getNewDownloadFields($download, $newUploadFiles);

        $newDownload = $this->getDownloadActivityDao()->create($newDownloadFields);

        $newMaterials = array();
        if (!empty($download['materials'])) {
            foreach ($download['materials'] as $courseMaterial) {
                $newMaterial = $courseMaterial;
                $newMaterial['syncId'] = $courseMaterial['id'];
                $newMaterial['courseSetId'] = $config['newActivity']['fromCourseSetId'];
                $newMaterial['courseId'] = $config['newActivity']['fromCourseId'];
                $newMaterial['lessonId'] = 0;
                $newMaterial['source'] = 'coursematerial';
                $newMaterial['userId'] = $config['newActivity']['fromUserId'];
                $newMaterial['fileId'] = empty($courseMaterial['fileId']) ? 0 : $newUploadFiles[$courseMaterial['fileId']]['id'];
                unset($newMaterial['id']);
                $newMaterials[] = $newMaterial;
            }
        }
        $this->getMaterialDao()->batchCreate($newMaterials);

        return $newDownload;
    }

    public function updateToLastedVersion($activity, $config = array())
    {
        $download = $activity[$activity['mediaType'].'Activity'];
        $newUploadFiles = $config['newUploadFiles'];
        $newDownloadFields = $this->getNewDownloadFields($download, $newUploadFiles);

        $existDownload = $this->getDownloadActivityDao()->search(array('syncId' => $newDownloadFields['syncId']), array(), 0, PHP_INT_MAX);
        if (!empty($existDownload)) {
            $newDownload = $this->getDownloadActivityDao()->update($existDownload[0]['id'], $newDownloadFields);
        } else {
            $newDownload = $this->getDownloadActivityDao()->create($newDownloadFields);
        }

        $newMaterials = array();
        $existMaterials = $this->getMaterialDao()->search(array(
            'courseSetId' => $config['newActivity']['fromCourseSetId'],
            'courseId' => $config['newActivity']['fromCourseId'],
            'source' => 'coursematerial',
            'fileIds' => ArrayToolkit::column($newUploadFiles, 'id'),
        ), array(), 0, PHP_INT_MAX);
        $existMaterials = ArrayToolkit::index($existMaterials, 'syncId');

        if (!empty($download['materials'])) {
            foreach ($download['materials'] as $courseMaterial) {
                $newMaterial = $courseMaterial;
                $newMaterial['syncId'] = $courseMaterial['id'];
                $newMaterial['courseSetId'] = $config['newActivity']['fromCourseSetId'];
                $newMaterial['courseId'] = $config['newActivity']['fromCourseId'];
                $newMaterial['lessonId'] = 0;
                $newMaterial['source'] = 'coursematerial';
                $newMaterial['userId'] = $config['newActivity']['fromUserId'];
                $newMaterial['fileId'] = empty($courseMaterial['fileId']) ? 0 : $newUploadFiles[$courseMaterial['fileId']]['id'];
                unset($newMaterial['id']);

                if (!empty($existMaterials[$newMaterial['syncId']])) {
                    $this->getMaterialDao()->update($existMaterials[$newMaterial['syncId']]['id'], $newMaterial);
                    continue;
                }

                $newMaterials[] = $newMaterial;
            }
        }
        $this->getMaterialDao()->batchCreate($newMaterials);

        return $newDownload;
    }

    protected function getNewDownloadFields($download, $newUploadFiles)
    {
        $newDownloadFileIds = array();
        foreach ($download['fileIds'] as $fileSyncId) {
            if (!empty($newUploadFiles[$fileSyncId])) {
                $newDownloadFileIds[] = (int) $newUploadFiles[$fileSyncId]['id'];
            } else {
                $newDownloadFileIds[] = $fileSyncId;
            }
        }

        return array(
            'mediaCount' => $download['mediaCount'],
            'fileIds' => $newDownloadFileIds,
            'syncId' => $download['id'],
        );
    }

    /**
     * @return DownloadActivityDao
     */
    protected function getDownloadActivityDao()
    {
        return $this->getBiz()->dao('Activity:DownloadActivityDao');
    }

    /**
     * @return CourseMaterialDao
     */
    protected function getMaterialDao()
    {
        return $this->getBiz()->dao('Course:CourseMaterialDao');
    }
}
