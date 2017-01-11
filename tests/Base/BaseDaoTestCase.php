<?php

namespace Tests\Base;

use Biz\BaseTestCase;

abstract class BaseDaoTestCase extends BaseTestCase
{
    public function searchTestUtil($dao, $testConditons, $testFields)
    {
        foreach ($testConditons as $testConditon) {
            $count = $dao->count($testConditon['condition']);
            $this->assertEquals($count, $testConditon['expectedCount']);

            $orderBy = empty($testConditon['orderBy']) ? array() : $testConditon['orderBy'];
            $results = $dao->search($testConditon['condition'], $orderBy, 0, 10);
            foreach ($results as $key => $result) {
                $this->assertArrayEquals($result, $testConditon['expectedResults'][$key], $testFields);
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
}
