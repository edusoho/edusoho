<?php

namespace Topxia\Service\Util;

class MySQLDumper
{
	private $connection;

    public function __construct ($connection)
    {
    	$this->connection = $connection;
    }

    /**
     * 获得数据库的所有表名
     * 
     * @return array 数据库所有表名的数组
     */
    public function getTables()
    {
    	$rows = $this->connection->executeQuery("SHOW TABLES")->fetchAll();
    	if (empty($rows)) {
    		return array();
    	}

    	$tables = array();
    	foreach ($rows as $row) {
    		$row = array_values($row);
    		$tables[] = $row[0];
    	}

    	return $tables;
    }

    /**
     * 导出整个数据库
     *
     * @param  string $dumpTable 导出表结构
     * @param  string $dumpData 导出表数据
     * 
     * @return [type] [description]
     */
    public function dumpDatabase($dumpTable = true, $dumpData = true)
    {
    	$sqls = array();
    	$tables = $this->getTables();
    	foreach ($tables as $table) {
    		if ($dumpTable === true) {
    			$sqls[] = $this->dumpTable($table);
    		}

    		if ($dumpData === true) {
    			$sqls[] = $this->dumpTableData($table);
    		}
    	}

    	return implode("\n", $sqls);
    }

    /**
     * 导出表名为$table的表结构
     * 
     * @param  string $table 表名
     * @return string        表结构SQL
     */
    public function dumpTable($table)
    {
        $tableCreateSql = $this->connection->fetchColumn("SHOW CREATE TABLE {$table}", array(), 1) . ";";
        $tableDropSql = "DROP TABLE IF EXISTS `{$table}`;";

        return $tableDropSql . "\n" . $tableCreateSql;
    }

    /**
     * 导出表名为$table的数据
     * 
     * @param  string $table 表名
     * @return string        表数据SQL
     */
    public function dumpTableData($table)
    {
        $sqls = array();
        $rows = $this->connection->fetchAll("SELECT * FROM {$table}");
        foreach ($rows as $row) {
            $values = '';
            foreach (array_values($row) as $value) {
                $value = str_replace("'", "''", $value);
                $values .= "'" . $value . "', ";
            }

            $values = substr($values, 0, -2);
            $sqls[] = 'INSERT INTO ' . $table . ' VALUES (' . $values . ');';
        }

        return implode("\n", $sqls);
    }

}