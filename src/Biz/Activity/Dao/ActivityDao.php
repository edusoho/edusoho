<?php

namespace Biz\Activity\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface ActivityDao extends GeneralDaoInterface
{
    public function findByCourseId($courseId);

    public function findByIds($ids);

    public function getByCopyIdAndCourseSetId($copyId, $courseSetId);

    public function findSelfVideoActivityByCourseIds($courseIds);

    public function findOverlapTimeActivitiesByCourseId($courseId, $newStartTime, $newEndTime, $excludeId = null);

    public function findFinishedLivesWithinTwoHours();

    public function getByMediaIdAndMediaTypeAndCopyId($mediaId, $mediaType, $copyId);

    public function getByMediaIdAndMediaType($mediaId, $mediaType);

    public function findActivitiesByMediaIdsAndMediaType($mediaIds, $mediaType);
}
