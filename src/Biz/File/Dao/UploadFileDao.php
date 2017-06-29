<?php

namespace Biz\File\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface UploadFileDao extends GeneralDaoInterface
{
    public function getByHashId($hash);

    public function getByGlobalId($globalId);

    public function getByConvertHash($hash);

    public function findByIds($ids);

    public function findCloudFilesByIds($ids);

    public function countByEtag($etag);

    public function deleteByGlobalId($globalId);

    public function waveUsedCount($id, $num);

    public function getByTargetType($targetType);

    public function findByTargetTypeAndTargetIds($targetType, $targetIds);

    public function findHeadLeaderFiles();
}
