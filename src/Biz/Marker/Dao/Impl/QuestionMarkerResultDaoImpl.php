<?php
namespace Biz\Marker\Dao\Impl;

use Biz\Marker\Dao\QuestionMarkerResultDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class QuestionMarkerResultDaoImpl extends GeneralDaoImpl implements QuestionMarkerResultDao
{
    protected $table = 'question_marker_result';

    public function deleteByQuestionMarkerId($questionMarkerId)
    {
        return $this->db()->delete($this->table, array('questionMarkerId' => $questionMarkerId));
    }

    public function findByUserIdAndMarkerId($userId, $markerId)
    {
        return $this->findByFields(array('userId' => $userId, 'markerId' => $markerId));
    }

    public function findByUserIdAndQuestionMarkerId($userId, $questionMarkerId)
    {
        return $this->findByFields(array('userId' => $userId, 'questionMarkerId' => $questionMarkerId));
    }

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updatedTime'),
            'orderbys'   => array('createdTime'),
            'conditions' => array(
                'userId = :userId',
                'markerId = :markerId',
                'questionMarkerId = :questionMarkerId'
            )
        );
    }
}
