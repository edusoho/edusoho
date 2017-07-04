<?php

namespace Codeages\Biz\Framework\Dao;

class BatchUpdateHelper
{
    private $dao;

    private $identifies = array();

    private $updateColumnsList = array();

    public function __construct($dao)
    {
        $this->dao = $dao;
    }

    public function add($identifyColumn, $identifyKey, $updateColumns)
    {
        if (!array_key_exists($identifyColumn, $this->identifies)) {
            $this->identifies[$identifyColumn] = array();
        }

        $this->identifies[$identifyColumn][] = $identifyKey;
        $this->updateColumnsList[$identifyColumn][] = $updateColumns;
    }

    public function flush()
    {
        foreach ($this->identifies as $identifyColumn => $identifyKeys) {
            $this->dao->batchUpdate($identifyKeys, $this->updateColumnsList[$identifyColumn], $identifyColumn);
            unset($this->identifies[$identifyColumn]);
            unset($this->updateColumnsList[$identifyColumn]);
        }
    }
}
