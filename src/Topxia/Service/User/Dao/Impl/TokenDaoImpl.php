<?php
namespace Topxia\Service\User\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\User\Dao\TokenDao;

class TokenDaoImpl extends BaseDao implements TokenDao
{
    protected $table = 'user_token';

	public function getToken($id)
	{
		$sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
	}

	public function findTokenByToken($token)
	{
		$sql = "SELECT * FROM {$this->table} WHERE token = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($token));
	}

	public function addToken(array $token)
	{
		$affected = $this->getConnection()->insert($this->table, $token);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert token error.');
        }
        return $this->getToken($this->getConnection()->lastInsertId());
	}

	public function deleteToken($id)
	{
		return $this->getConnection()->delete($this->table, array('id' => $id));
	}

	public function searchTokenCount($conditions)
	{
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
	}

	private function _createSearchQueryBuilder($conditions)
	{
        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'user_token')
            ->andWhere('type = :type');
        
        return $builder;
	}
}