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
        return $this->getByFields(['answerSceneId' => $answerSceneId]);
    }

    public function findByAnswerSceneIds($answerSceneIds)
    {
        return $this->findInField('answerSceneId', $answerSceneIds);
    }

    public function declares()
    {
        return [
            'conditions' => [
                'id = :id',
                'id IN (:ids)',
                /*S2B2C增加syncId*/
                'syncId = :syncId',
                'syncId in (:syncIds)',
                'syncId > :syncIdGT',
                /*END*/
            ],
            'serializes' => [
                'finishCondition' => 'json',
            ],
        ];
    }
}
