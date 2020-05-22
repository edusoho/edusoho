<?php

namespace Biz\Activity\Dao\Impl;

use Biz\Activity\Dao\TestpaperActivityDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class TestpaperActivityDaoImpl extends AdvancedDaoImpl implements TestpaperActivityDao
{
    protected $table = 'activity_testpaper';

    public function findByIds($ids)
    {
        return $this->findInField('id', $ids);
    }

    public function findByMediaIds($mediaIds)
    {
        return $this->findInField('mediaId', $mediaIds);
    }

    public function getActivityByAnswerSceneId($answerSceneId)
    {
        return $this->getByFields(array('answerSceneId' => $answerSceneId));
    }

    public function findByAnswerSceneIds($answerSceneIds)
    {
        return $this->findInField('answerSceneId', $answerSceneIds);
    }

    public function declares()
    {
        $declares['conditions'] = array(
            'id = :id',
            'id IN (:ids)',
        );

        $declares['serializes'] = array(
            'finishCondition' => 'json',
        );

        return $declares;
    }
}
