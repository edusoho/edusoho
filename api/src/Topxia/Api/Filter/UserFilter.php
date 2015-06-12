<?php

namespace Topxia\Api\Filter;

class UserFilter implements Filter
{
	public function filter(array &$data)
	{
		unset($data['password']);
		unset($data['salt']);
		unset($data['payPassword']);
		unset($data['payPasswordSalt']);
		$data['createdTime'] = date('c', $data['createdTime']);

		return $data;
	}

}

