<?php

namespace Codeages\Biz\Framework\Dao;

class BatchCreateHelper implements BatchHelperInterface
{
    /**
     * @var AdvancedDaoInterface
     */
    private $dao;

    private $rows = array();

    public function __construct($dao)
    {
        $this->dao = $dao;
    }

    public function add($row)
    {
        $this->rows[] = $row;
    }

    public function flush()
    {
        $this->dao->batchCreate($this->rows);
        unset($this->rows);
    }
}
