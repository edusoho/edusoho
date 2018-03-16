<?php

namespace Codeages\Biz\Framework\Dao;

interface GeneralDaoInterface extends DaoInterface
{
    public function create($fields);

    public function update($identifier, array $fields);

    public function delete($id);

    public function get($id, array $options = array());

    public function search($conditions, $orderBys, $start, $limit, $columns = array());

    public function count($conditions);

    public function wave(array $ids, array $diffs);

    public function table();
}
