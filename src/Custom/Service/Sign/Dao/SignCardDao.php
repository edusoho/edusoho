<?php

namespace Custom\Service\Sign\Dao;

interface SignCardDao
{
    public function addSignCard($signCard);

    public function getSignCardByUserId($userId);

    public function updateSignCard($id, $fields);

    public function waveCrad($id, $value);

}