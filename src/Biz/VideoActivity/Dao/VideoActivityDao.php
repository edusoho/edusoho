<?php
/**
 * User: Edusoho V8
 * Date: 01/11/2016
 * Time: 14:13
 */

namespace Biz\VideoActivity\Dao;


interface  VideoActivityDao
{
    public function updateByActivityId($activityId, $fields);

    public function getByActivityId($activityId);
}