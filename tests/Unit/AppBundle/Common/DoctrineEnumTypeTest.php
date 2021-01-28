<?php

namespace Tests\Unit\AppBundle\Common;

use Biz\BaseTestCase;
use Doctrine\DBAL\Platforms\PostgreSqlPlatform;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\DBAL\Types\Type;

class DoctrineEnumTypeTest extends BaseTestCase
{
    public function testGetSqlDeclaration()
    {
        $doctrineEnumType = $this->getDoctrineEnumType();
        $result = $doctrineEnumType->getSqlDeclaration(
            array(
                'comment' => 'test1,test2,test3,test4',
                'name' => '',
            ),
            new SqlitePlatform()
        );
        $this->assertEquals("TEXT CHECK( IN ('test1', 'test2', 'test3', 'test4'))", $result);

        $result = $doctrineEnumType->getSqlDeclaration(
            array(
                'comment' => 'test1,test2,test3,test4',
                'name' => '',
            ),
            new PostgreSqlPlatform()
        );
        $this->assertEquals("VARCHAR(255) CHECK( IN ('test1', 'test2', 'test3', 'test4'))", $result);
    }

    public function testRemoveDoctrineTypeFromComment()
    {
        $doctrineEnumType = $this->getDoctrineEnumType();
        $result = $doctrineEnumType->removeDoctrineTypeFromComment('(DC2Type:enum)(DC2Type:enun)', 'enum');
        $this->assertEquals('(DC2Type:enun)', $result);
    }

    public function testGetName()
    {
        $doctrineEnumType = $this->getDoctrineEnumType();
        $result = $doctrineEnumType->getName();
        $this->assertEquals('enum', $result);
    }

    private function getDoctrineEnumType()
    {
        if (!Type::hasType('doctrineEnum')) {
            Type::addType('doctrineEnum', 'AppBundle\Common\DoctrineEnumType');
        }

        return Type::getType('doctrineEnum');
    }
}
