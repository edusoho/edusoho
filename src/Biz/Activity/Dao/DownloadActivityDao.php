<?php
/**
 * User: Edusoho V8
 * Date: 03/11/2016
 * Time: 15:41.
 */

namespace Biz\Activity\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface DownloadActivityDao extends AdvancedDaoInterface
{
    public function findByIds($Ids);
}
