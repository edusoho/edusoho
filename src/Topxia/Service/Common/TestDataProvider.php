<?php

namespace Topxia\Service\Common;

use \Exception;

abstract class TestDataProvider {

    protected $createSql;

    protected $dropSql;

    protected $emptySql;

    protected $rows ;

    public function __construct() {
        if (empty($this->rows)) {
            throw new Exception(__CLASS__ . ".rows is empty");
        }

        if (empty($this->createSql)) {
            throw new Exception(__CLASS__ . ".createSql is empty");
        }

        if (empty($this->dropSql)) {
            throw new Exception(__CLASS__ . ".dropSql is empty");
        }

        if (empty($this->emptySql)) {
            throw new Exception(__CLASS__ . ".emptySql is empty");
        }
    }

    public function row() {
        $key = array_rand($this->rows);
        return $this->rows[$key];
    }

    public function rows($num = 2) {
        $rows = $this->rows;
        shuffle($rows);
        return array_slice($rows, 0, $num);
    }

    public function all() {
        return $this->rows;
    }

    public function getCreateSql() {
        return $this->createSql;
    }

    public function getDropSql() {
        return $this->dropSql;
    }

    public function getEmptySql() {
        return $this->emptySql;
    }
}