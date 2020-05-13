<?php

namespace Biz\S2B2C\Sync\Component;

use AppBundle\Common\ArrayToolkit;
use Biz\File\Dao\UploadFileDao;
use Biz\File\Dao\UploadFileInitDao;
use Biz\S2B2C\Service\S2B2CFacadeService;

class UploadFileSync extends AbstractEntitySync
{
    protected function syncEntity($source, $config = [])
    {
        $uploadFiles = $source['uploadFiles'];
        if (empty($uploadFiles)) {
            return [];
        }
        $newCourse = $config['newCourse'];
        $syncIds = ArrayToolkit::column($uploadFiles, 'id');

        $this->dealUploadFileData($uploadFiles, $config);

        return $this->getUploadFileDao()->search(['syncIds' => $syncIds], [], 0, PHP_INT_MAX);
    }

    protected function updateEntityToLastedVersion($source, $config = [])
    {
        $uploadFiles = $source['uploadFiles'];
        $newCourse = $config['newCourse'];
        $newCourseSetId = $newCourse['courseSetId'];

        $existsFiles = $this->getUploadFileDao()->search(['targetId' => $newCourseSetId], [], 0, PHP_INT_MAX);
        if (empty($uploadFiles)) {
            foreach ($existsFiles as $existsFile) {
                $this->getUploadFileDao()->delete($existsFile['id']);
                $this->getUploadFileInitDao()->delete($existsFile['id']);
            }

            return [];
        }
        $syncIds = ArrayToolkit::column($uploadFiles, 'id');

        $this->dealUploadFileData($uploadFiles, $config);

        $needDeleteUploadFileSyncIds = array_values(array_diff(ArrayToolkit::column($existsFiles, 'syncId'), $syncIds));
        if (!empty($existsFiles) && !empty($needDeleteUploadFileSyncIds)) {
            $needDeleteUploadFiles = $this->getUploadFileDao()->search(['targetId' => $newCourseSetId, 'syncIds' => $needDeleteUploadFileSyncIds], [], 0, PHP_INT_MAX);
            foreach ($needDeleteUploadFiles as $needDeleteUploadFile) {
                $this->getUploadFileDao()->delete($needDeleteUploadFile['id']);
                $this->getUploadFileInitDao()->delete($needDeleteUploadFile['id']);
            }
        }

        return $this->getUploadFileDao()->search(['syncIds' => $syncIds], [], 0, PHP_INT_MAX);
    }

    protected function dealUploadFileData($uploadFiles, $config)
    {
        $user = $this->biz['user'];
        $newCourse = $config['newCourse'];
        $newCourseSetId = $newCourse['courseSetId'];

        $existsFiles = $this->getUploadFileDao()->search(['storage' => 'supplier'], null, 0, PHP_INT_MAX);
        $existsFiles = ArrayToolkit::index($existsFiles, 'hashId');

        $s2b2cConfig = $this->getS2B2CFacadeService()->getS2B2CConfig();

        foreach ($uploadFiles as $uploadFile) {
            $uploadFile['storage'] = 'supplier';
            if (!empty($existsFiles[$uploadFile['hashId']])) {
                $randString = substr(base_convert(sha1(uniqid(mt_rand(), true)), 16, 36), 0, 12);
                $uploadFile['s2b2cGlobalId'] = $uploadFile['globalId'];
                $uploadFile['s2b2cHashId'] = $uploadFile['hashId'];
                $uploadFile['globalId'] = 's2b2c-global-course-'.$randString;
                $uploadFile['hashId'] = 's2b2c-hash-course-'.$randString;
            }

            $newUploadFile = $uploadFile;
            $newUploadFile['createdUserId'] = $user['id'];
            $newUploadFile['syncId'] = $uploadFile['id'];
            $newUploadFile['targetId'] = $newCourseSetId;
            $newUploadFile['createdTime'] = time();
            $newUploadFile['convertStatus'] = 'success';
            $newUploadFile['convertStatus'] = 'success';
            $newUploadFile['originPlatform'] = $newCourse['platform'];
            $newUploadFile['originPlatformId'] = $s2b2cConfig['supplierId'];

            unset($newUploadFile['id']);
            unset($newUploadFile['updatedUserId']);
            unset($newUploadFile['updatedTime']);

            $uploadFileInit = $this->filterUploadFileInit($newUploadFile);
            $uploadFileInit = $this->getUploadFileInitDao()->create($uploadFileInit);

            $newUploadFile['id'] = $uploadFileInit['id'];
            $this->getUploadFileDao()->create($newUploadFile);
        }
    }

    protected function filterUploadFileInit($uploadFile)
    {
        return ArrayToolkit::parts($uploadFile, [
            'globalId',
            'status',
            'hashId',
            'targetId',
            'targetType',
            'filename',
            'ext',
            'fileSize',
            'etag',
            'length',
            'convertHash',
            'convertStatus',
            'metas',
            'metas2',
            'type',
            'storage',
            'convertParams',
            'createdUserId',
            'createdTime',
        ]);
    }

    protected function getFields()
    {
        return [
            'type',
            'number',
            'seq',
            'title',
            'status',
            'isOptional',
            'published_number',
        ];
    }

    /**
     * @return UploadFileInitDao
     */
    protected function getUploadFileInitDao()
    {
        return $this->biz->dao('File:UploadFileInitDao');
    }

    /**
     * @return UploadFileDao
     */
    protected function getUploadFileDao()
    {
        return $this->biz->dao('File:UploadFileDao');
    }

    /**
     * @return S2B2CFacadeService
     */
    protected function getS2B2CFacadeService()
    {
        return $this->biz->service('S2B2C:S2B2CFacadeService');
    }
}
