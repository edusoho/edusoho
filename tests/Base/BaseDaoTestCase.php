<?php

namespace Tests\Base;

use Biz\BaseTestCase;

abstract class BaseDaoTestCase extends BaseTestCase
{
    public function searchTestUtil($dao, $testConditons, $testFields)
    {
        foreach ($testConditons as $testConditon) {
            $count = $dao->count($testConditon['condition']);
            $this->assertEquals($testConditon['expectedCount'], $count);

            $orderBy = empty($testConditon['orderBy']) ? array() : $testConditon['orderBy'];
            $results = $dao->search($testConditon['condition'], $orderBy, 0, 10);
            foreach ($results as $key => $result) {
                $this->assertArrayEquals($testConditon['expectedResults'][$key], $result, $testFields);
            }
        }
    }

    abstract protected function getDefaultMockFields();

    protected function mockDataObject($fields = array())
    {
        $fields = array_merge($this->getDefaultMockFields(), $fields);
        return $this->getDao()->create($fields);
    }

    protected function getCompareKeys()
    {
        return array_keys($this->getDefaultMockFields());
    }

    protected function getDao()
    {
        $class = new \ReflectionClass($this);

        $namespace = $class->getNamespaceName();
        $packageName = explode('\\', $namespace)[1];

        $testName = $class->getShortName();
        if (strpos($testName, 'Test', strlen($testName) - 4)) {
            $daoName = substr($testName, 0, strlen($testName) - 4);
        } else {
            throw new \Exception('classname must be up to standard, which is to end up with \'Test\'');
        }

        return $this->getBiz()->dao("{$packageName}:{$daoName}");
    }

    /**
     * 用在对createdTime和id等排序时
     * 第二个参数condition内部条件的顺序是唯一确定，不可改变的，这很关键
     */
    protected function sort(array &$tar, array $condition)
    {
        array_walk($condition, function (&$val) {
            if (strpos($val, 'ASC') !== false) {
                $val = 1;
            } elseif (strpos($val, 'DESC') !== false) {
                $val = -1;
            }
        });

        usort($tar, function ($a, $b) use ($condition) {
            foreach ($condition as $key => $val) {
                if ($a[$key] == $b[$key]) {
                    continue;
                }
                return $a[$key] < $b[$key] ? -1 * $val : $val;
            }
        });
    }
}
