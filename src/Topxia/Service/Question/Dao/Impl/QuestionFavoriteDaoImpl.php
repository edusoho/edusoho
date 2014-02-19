<?php

namespace Topxia\Service\Question\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Doctrine\DBAL\Query\QueryBuilder,
    Doctrine\DBAL\Connection;

class QuestionFavoriteDaoImpl extends BaseDao
{
	protected $table = "question_favorite";

	public function getFavorite($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

	public function addFavorite ($favorite)
	{
		$favorite = $this->getConnection()->insert($this->table, $favorite);
        if ($favorite <= 0) {
            throw $this->createDaoException('Insert favorite error.');
        }
        return $this->getFavorite($this->getConnection()->lastInsertId());
	}

	public function getFavoriteByQuestionIdAndTargetAndUserId ($favorite)
	{
        $sql = "SELECT * FROM {$this->table} WHERE questionId = ? AND target = ? AND userId = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($favorite['questionId'], $favorite['target'], $favorite['userId'])) ? : null;
	}

	public function deleteFavorite ($favorite)
    {
        return $this->getConnection()->delete($this->table, $favorite);
    } 

    public function findFavoriteQuestionsByUserId ($id, $start, $limit)
    {
    	$this->filterStartLimit($start, $limit);
        $sql = "SELECT * FROM {$this->table} WHERE `userId` = ? ORDER BY createdTime DESC LIMIT {$start}, {$limit}";
        return $this->getConnection()->fetchAll($sql, array($id)) ? : array();
    }

    public function findFavoriteQuestionsCountByUserId ($id)
    {
        $sql = "SELECT COUNT(id) FROM {$this->table} WHERE `userId` = ?";
        return $this->getConnection()->fetchColumn($sql, array($id));
    }

    public function findAllFavoriteQuestionsByUserId ($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE `userId` = ? ";
        return $this->getConnection()->fetchAll($sql, array($id)) ? : array();
    }
}