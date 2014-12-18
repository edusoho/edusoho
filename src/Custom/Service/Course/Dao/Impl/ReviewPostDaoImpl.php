<?php 
namespace Custom\Service\Course\Dao\Impl;
use Topxia\Service\Common\BaseDao;
use Custom\Service\Course\Dao\ReviewPostDao;
class ReviewPostDaoImpl extends BaseDao implements ReviewPostDao
{
	public function getReviewPost($id)
	{
		$sql = "SELECT * FROM {$this->getTablename()} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
	}

	public function addReviewPost($reviewPost)
	{
		$affected = $this->getConnection()->insert(self::TABLENAME, $reviewPost);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert review_post error.');
        }
        return $this->getReviewPost($this->getConnection()->lastInsertId());
	}

	public function findReviewPostsByReviewIds(array $reviewIds)
	{
		if(empty($reviewIds)){
            return array();
        }
        $marks = str_repeat('?,', count($reviewIds) - 1) . '?';
        $sql ="SELECT * FROM {$this->getTablename()} WHERE reviewId IN ({$marks}) ORDER BY createdTime DESC;";
        return $this->getConnection()->fetchAll($sql, $reviewIds);
	}
	
	public function deleteReviewPost($id)
	{
		return $this->getConnection()->delete(self::TABLENAME, array('id' => $id));
	}

	public function updateReviewPost($id,$fields)
	{
		$this->getConnection()->update(self::TABLENAME, $fields, array('id' => $id));
        return $this->getReviewPost($id);
	}
	
	private function getTablename()
    {
        return self::TABLENAME;
    }
}
