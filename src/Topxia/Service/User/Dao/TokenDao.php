<?php

namespace Topxia\Service\User\Dao;

interface TokenDao
{

	public function getToken($id);

	public function findTokenByToken($token);

	public function addToken(array $token);

	public function deleteToken($id);

	public function searchTokenCount($conditions);

}