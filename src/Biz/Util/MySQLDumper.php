<?php

namespace Biz\Util;

use AppBundle\Common\Exception\AccessDeniedException;
use Biz\BaseService;

class MySQLDumper extends BaseService
{
    public $target = '';
    private $dbSettings = array();
    private $internelSetting = array(
        'exclude' => array(),
        'isDropTable' => true,
        'hasTransaction' => true,
        'lockread' => true,
        'lockwrite' => true,
        'isextend' => true,
    );

    private $connection;

    public function __construct($connection, $settings = array())
    {
        $this->connection = $connection;
        $this->dbSettings = $settings;
        $this->connection->exec('SET NAMES utf8');
    }

    protected function getSet($key)
    {
        if (array_key_exists($key, $this->dbSettings)) {
            return $this->dbSettings[$key];
        } else {
            return $this->internelSetting[$key];
        }
    }

    public function export($target)
    {
        $target .= '.gz';

        if (!is_writable(dirname($target))) {
            throw new AccessDeniedException('无导出目录写权限，无法导出数据库', 1);
        }

        $tables = array();
        foreach ($this->connection->query('SHOW TABLES') as $table) {
            $tables[] = current($table);
        }

        $file = gzopen($target, 'wb');

        $this->lineWrite($file, "-- --------------------------------------------------\n");
        $this->lineWrite($file, '-- ---------------- 导出数据库'."\n");
        $this->lineWrite($file, '-- ---------------- 时间:'.date('Y-m-d H:i:s')."\n\n");
        $this->lineWrite($file, '-- ---------------- 数据库:'."{$this->connection->getDatabase()}"." \n\n");
        $this->lineWrite($file, '-- ---------------- 主机:'."{$this->connection->getHost()} "."\n\n");
        $this->lineWrite($file, "-- --------------------------------------------------\n\n\n");

        foreach ($tables as $table) {
            if ($this->exportTableCreateSql($file, $table)) {
                if (in_array($table, $this->getSet('exclude'), true)) {
                    continue;
                }
                try {
                    $this->exportValues($file, $table);
                } catch (\Exception $e) {
                    $this->connection->rollback();
                    if ($this->getSet('lockread')) {
                        $this->connection->exec('UNLOCK TABLES');
                    }
                    throw new \Exception($e->getMessage());
                }
            }
        }
        gzclose($file);

        return $target;
    }

    protected function lineWrite($file, $line)
    {
        gzwrite($file, $line);
    }

    protected function exportTableCreateSql($file, $table)
    {
        $sql = "SHOW CREATE TABLE {$table}";
        foreach ($this->connection->query($sql) as $row) {
            if (isset($row['Create Table'])) {
                $line = "-- --------------------------------------------------\n\n";
                $line .= sprintf('-- -------------- 表:%s 的表结构  -------------- ', $table)."\n\n";
                $line .= "-- --------------------------------------------------\n\n";

                if ($this->getSet('isDropTable')) {
                    $line .= " DROP TABLE IF EXISTS {$table} ; \n";
                }
                $line .= $row['Create Table'];
                $line .= ";\n\n";
                $this->lineWrite($file, $line);

                return true;
            }
            if (isset($row['Create View'])) {
                $line = "-- --------------------------------------------------\n\n";
                $line .= sprintf('-- -------------- 视图:%s 的表结构  --------------', $table)."\n\n";
                $line .= "-- --------------------------------------------------\n\n";
                $line .= $row['Create View'];
                $line .= ";\n\n";

                $this->lineWrite($file, $line);

                return false;
            }
        }
    }

    protected function exportValues($file, $table)
    {
        $this->lineWrite($file, sprintf('-- ----------- %s 的数据 ---------', $table)."\n\n");

        if ($this->getSet('hasTransaction')) {
            $this->connection->setTransactionIsolation(3);
            $this->connection->beginTransaction();
        }
        if ($this->getSet('lockread')) {
            $this->connection->exec("LOCK TABLES `$table` READ LOCAL");
        }

        if ($this->getSet('lockwrite')) {
            $this->lineWrite($file, "LOCK TABLES `$table` WRITE;\n");
        }

        $insertExclude = true;
        $sql = "SELECT * FROM `$table`";
        foreach ($this->connection->query($sql, \PDO::FETCH_NUM) as $row) {
            $vals = array();
            foreach ($row as $val) {
                $vals[] = is_null($val) ? 'NULL' :
                $this->connection->quote($val);
            }
            if ($insertExclude || !$this->getSet('isextend')) {
                $this->lineWrite($file,
                    "INSERT INTO {$table} VALUES (".implode(',', $vals).')');
                $insertExclude = false;
            } else {
                $this->lineWrite($file, ',('.implode(',', $vals).')');
            }
        }
        if (!$insertExclude) {
            $this->lineWrite($file, ";\n\n");
        }
        if ($this->getSet('lockwrite')) {
            $this->lineWrite($file, "UNLOCK TABLES;\n");
        }
        if ($this->getSet('hasTransaction')) {
            $this->connection->commit();
        }
        if ($this->getSet('lockread')) {
            $this->connection->exec('UNLOCK TABLES');
        }
        $this->lineWrite($file, "\n\n");
        $this->lineWrite($file, sprintf('-- ----------- %s 的数据结束 ---------', $table)."\n\n");
    }
}
