<?php
/**
 * Created by PhpStorm.
 * User: Simon
 * Date: 12/12/2016
 * Time: 18:09.
 */

namespace Biz\Taxonomy\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface TagDao extends GeneralDaoInterface
{
    public function findByIds(array $ids);

    public function findByNames(array $names);

    public function findAll($start, $limit);

    public function getByName($name);

    public function findByLikeName($name);

    public function getAllCount();
}
