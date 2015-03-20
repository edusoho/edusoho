<?php
namespace Topxia\Service\User\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\User\Dao\TokenDao;

class TokenDaoImpl extends BaseDao implements TokenDao
{
    protected $table = 'user_token';

    private $serializeFields = array(
            'data' => 'phpserialize',
    );

	public function getToken($id)
	{
		$sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        $token = $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
        return $token ? $this->createSerializer()->unserialize($token, $this->serializeFields) : null;
	}

    public function getTokenByToken($token)
    {
        $sql = "SELECT * FROM {$this->table} WHERE token = ? LIMIT 1";
        $token = $this->getConnection()->fetchAssoc($sql, array($token));
        return $token ? $this->createSerializer()->unserialize($token, $this->serializeFields) : null;
    }

	public function findTokenByToken($token)
	{
		$sql = "SELECT * FROM {$this->table} WHERE token = ? LIMIT 1";
        $token = $this->getConnection()->fetchAssoc($sql, array($token));
        return $token ? $this->createSerializer()->unserialize($token, $this->serializeFields) : null;
	}

	public function addToken(array $token)
	{
        $token = $this->createSerializer()->serialize($token, $this->serializeFields);
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

    public function waveRemainedTimes($id, $diff)
    {
        $sql = "UPDATE {$this->table} SET remainedTimes = remainedTimes + ? WHERE id = ? LIMIT 1";
        return $this->getConnection()->executeQuery($sql, array($diff, $id));
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