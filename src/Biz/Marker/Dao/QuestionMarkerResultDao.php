<?php
namespace Biz\Marker\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface QuestionMarkerResultDao extends GeneralDaoInterface
{
    public function deleteByQuestionMarkerId($questionMarkerId);

    public function findByUserIdAndMarkerId($userId, $markerId);

    public function findByUserIdAndQuestionMarkerId($userId, $questionMarkerId);
}
