<?php

namespace Tests\Unit\AppBundle\Util;

use Biz\BaseTestCase;
use AppBundle\Util\CategoryBuilder;

class CategoryBuilderTest extends BaseTestCase
{
    public function testBuildChoices()
    {
        $this->mockBiz(
            'Taxonomy:CategoryService',
            array(
                array(
                    'functionName' => 'getGroupByCode',
                    'withParams' => array('xxx'),
                    'returnValue' => null,
                ),
                array(
                    'functionName' => 'getGroupByCode',
                    'withParams' => array('course'),
                    'returnValue' => array(
                        'id' => 1,
                        'code' => 'course',
                        'name' => '课程分类',
                        'depth' => 3,
                    ),
                ),
                array(
                    'functionName' => 'getCategoryTree',
                    'returnValue' => json_decode('[{
                        "id": "1",
                        "code": "default",
                        "name": "默认分类",
                        "icon": "",
                        "path": "",
                        "weight": "0",
                        "groupId": "1",
                        "parentId": "0",
                        "orgId": "1",
                        "orgCode": "1.",
                        "description": null,
                        "depth": 1
                    }, {
                        "id": "3",
                        "code": "zifenlei",
                        "name": "子分类",
                        "icon": "",
                        "path": "",
                        "weight": "0",
                        "groupId": "1",
                        "parentId": "1",
                        "orgId": "1",
                        "orgCode": "1.",
                        "description": "",
                        "depth": 2
                    }]', true),
                ),
            )
        );
        $builder = new CategoryBuilder();
        $result = $builder->buildChoices('xxx');
        $this->assertEmpty($result);

        $result = $builder->buildChoices('course');
        var_dump($result);
        $this->assertEquals('默认分类', $result[1]);
        $this->assertEquals('　子分类', $result[3]);
    }
}
