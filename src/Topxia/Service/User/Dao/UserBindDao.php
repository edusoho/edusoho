<?php

namespace Topxia\Service\User\Dao;

interface UserBindDao
{
	public function getBind($id);

	public function getBindByFromId($fromId);
	
	public function getBindByTypeAndFromId($type, $fromId);

	public function getBindByToIdAndType($type, $toId);

    public function getBindByToken($token);
    
	public function addBind($bind);

    public function deleteBind($id);

    public function findBindsByToId($toId);

}