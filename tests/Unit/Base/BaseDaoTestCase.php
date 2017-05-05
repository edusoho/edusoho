<?php

namespace Tests\Unit\Base;

use Biz\BaseTestCase;

abstract class BaseDaoTestCase extends BaseTestCase
{
    public function searchTestUtil($dao, $testConditions, $testFields)
    {
        foreach ($testConditions as $testCondition) {
            $count = $dao->count($testCondition['condition']);
            $this->assertEquals($testCondition['expectedCount'], $count);

            $orderBy = empty($testCondition['orderBy']) ? array() : $testCondition['orderBy'];
            $results = $dao->search($testCondition['condition'], $orderBy, 0, 10);
            foreach ($results as $key => $result) {
                $this->assertArrayEquals($testCondition['expectedResults'][$key], $result, $testFields);
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
        $packageNameArr = explode('\\', $namespace);
        $packageName = $packageNameArr[2];

        $testName = $class->getShortName();
        if (strpos($testName, 'Test', strlen($testName) - 4)) {
            $daoName = substr($testName, 0, strlen($testName) - 4);
        } else {
            throw new \Exception('classname must be up to standard, which is to end up with \'Test\'');
        }

        return $this->createDao("{$packageName}:{$daoName}");
    }

    /**
     * 用在对createdTime和id等排序时
     * 第二个参数condition内部条件的顺序是唯一确定，不可改变的，这很关键.
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
