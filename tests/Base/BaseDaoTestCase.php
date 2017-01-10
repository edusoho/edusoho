<?php

namespace Tests\Base;

use Biz\BaseTestCase;

class BaseDaoTestCase extends BaseTestCase
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

    protected function mockDataObject($fields = array())
    {
        if (in_array('getDefaultMockFields', get_class_methods($this))) {
            $fields = array_merge($this->getDefaultMockFields(), $fields);
            return $this->getDao()->create($fields);
        } else {
            throw new \Exception('method getDefaultMockFields doesn\'t exist!');
        }
    }

    protected function getCompareKeys()
    {
        if (in_array('getDefaultMockFields', get_class_methods($this))) {
            return array_keys($this->getDefaultMockFields());
        } else {
            throw new \Exception('method getDefaultMockFields doesn\'t exist!');
        }
    }

    protected function getDao()
    {
        $class = new \ReflectionClass($this);

        $namespace = $class->getNamespaceName();
        $packageName = explode('\\', $namespace)[1];

        $testName = $class->getShortName();
        if (strpos($testName, 'Test', strlen($testName) - 4)) {
            $daoName = substr($testName, 0, strlen($testName) - 4);
        }

        return $this->getBiz()->dao("{$packageName}:{$daoName}");
    }
}
