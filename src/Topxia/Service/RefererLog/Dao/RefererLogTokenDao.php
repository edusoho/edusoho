<?php
namespace Topxia\Service\RefererLog\Dao;

interface RefererLogTokenDao
{
    public function geTokenByUv($uv);

    public function addToken($userRefererOrder);

    public function updateToken($id, $fields);
}
