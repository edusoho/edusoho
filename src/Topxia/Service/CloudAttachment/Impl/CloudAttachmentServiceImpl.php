<?php
namespace Topxia\Service\CloudAttachment\Impl;

use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\BaseService;
use Topxia\Service\CloudAttachment\CloudAttachmentService;

class CloudAttachmentServiceImpl extends BaseService implements CloudAttachmentService
{
    static $implementor = array(
        'local' => 'File.LocalFileImplementor',
        'cloud' => 'File.CloudFileImplementor'
    );

    public function searchFileCount($conditions)
    {
        $conditions = $this->_prepareSearchConditions($conditions);

        $files     = $this->getUploadFileDao()->searchFiles($conditions, array('createdTime', 'DESC'), 0, PHP_INT_MAX);
        $globalIds = ArrayToolkit::column($files, 'globalId');

        if (empty($globalIds)) {
            return 0;
        }

        $cloudFileConditions = array(
            'processStatus' => $conditions['processStatus'],
            'nos'           => implode(',', $globalIds)
        );

        $cloudFiles = $this->getFileImplementor('cloud')->search($cloudFileConditions);

        return $cloudFiles['count'];
    }

    public function searchFiles($conditions, $sort, $start, $limit)
    {
        $conditions = $this->_prepareSearchConditions($conditions);

        $files     = $this->getUploadFileDao()->searchFiles($conditions, $sort, 0, PHP_INT_MAX);
        $globalIds = ArrayToolkit::column($files, 'globalId');

        if (empty($globalIds)) {
            return array();
        }

        $cloudFileConditions = array(
            'processStatus' => $conditions['processStatus'],
            'nos'           => implode(',', $globalIds),
            'start'         => $start,
            'limit'         => $limit
        );
        if (empty($conditions['processStatus'])) {
            unset($cloudFileConditions['processStatus']);
        }
        $cloudFiles = $this->getFileImplementor('cloud')->search($cloudFileConditions);

        return $cloudFiles['data'];
    }

    protected function _prepareSearchConditions($conditions)
    {
        $conditions['storage']       = 'cloud';
        $conditions['existGlobalId'] = 0;

        if (isset($conditions['startDate']) && !empty($conditions['startDate'])) {
            $conditions['startDate'] = strtotime($conditions['startDate']);
        } else {
            unset($conditions['startDate']);
        }

        if (isset($conditions['endDate']) && !empty($conditions['endDate'])) {
            $conditions['endDate'] = strtotime($conditions['endDate']);
        } else {
            unset($conditions['endDate']);
        }

        if (isset($conditions['useStatus'])) {
            if ($conditions['useStatus'] == 'unused') {
                $conditions['endCount'] = 1;
            }

            if ($conditions['useStatus'] == 'used') {
                $conditions['startCount'] = 1;
            }
        }

        return $conditions;
    }

    protected function getUploadFileDao()
    {
        return $this->createDao('File.UploadFileDao');
    }

    protected function getFileImplementor($key)
    {
        if (!array_key_exists($key, self::$implementor)) {
            throw $this->createServiceException(sprintf("`%s` File Implementor is not allowed.", $key));
        }

        return $this->createService(self::$implementor[$key]);
    }
}
