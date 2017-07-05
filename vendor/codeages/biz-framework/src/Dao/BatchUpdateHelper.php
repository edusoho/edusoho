<?php

namespace Codeages\Biz\Framework\Dao;

use Codeages\Biz\Framework\Service\Exception\NotFoundException;

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
        $this->updateColumnsList[$identifyColumn][$identifyKey] = $updateColumns;
    }

    public function get($identifyColumn, $identifyKey)
    {
        if (isset($this->updateColumnsList[$identifyColumn])
            && isset($this->updateColumnsList[$identifyColumn][$identifyKey])) {
            return $this->updateColumnsList[$identifyColumn][$identifyKey];
        }

        throw new NotFoundException('Not Found');
    }

    public function findIdentifyKeys($identifyColumn)
    {
        if (isset($this->identifies[$identifyColumn])) {
            return $this->identifies[$identifyColumn];
        }

        throw new NotFoundException('Not Found');
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
