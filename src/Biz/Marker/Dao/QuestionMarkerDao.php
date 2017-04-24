<?php

namespace Biz\Marker\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface QuestionMarkerDao extends GeneralDaoInterface
{
    public function getMaxSeqByMarkerId($id);

    public function merge($sourceMarkerId, $targetMarkerId, $maxSeq);

    public function findByIds($ids);

    public function findByMarkerId($markerId);

    public function findByMarkerIds($markerIdss);

    public function findByQuestionId($questionId);

    public function waveSeqBehind($markerId, $seq);

    public function waveSeqForward($markerId, $seq);
}
