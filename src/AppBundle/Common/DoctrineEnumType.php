<?php

namespace AppBundle\Common;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\PostgreSqlPlatform;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\DBAL\Types\Type;

class DoctrineEnumType extends Type
{
    public function getSqlDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        $comment = $this->removeDoctrineTypeFromComment($fieldDeclaration['comment'], 'enum');
        $values = explode(',', $comment);
        $values = implode(
            ', ',
            array_map(
                function ($value) {
                    return "'{$value}'";
                },
                $values
            )
        );

        if ($platform instanceof SqlitePlatform) {
            return sprintf('TEXT CHECK(%s IN (%s))', $fieldDeclaration['name'], $values);
        }

        if ($platform instanceof PostgreSqlPlatform) {
            return sprintf('VARCHAR(255) CHECK(%s IN (%s))', $fieldDeclaration['name'], $values);
        }

        return sprintf('ENUM(%s)', $values);
    }

    public function removeDoctrineTypeFromComment($comment, $type)
    {
        return str_replace('(DC2Type:'.$type.')', '', $comment);
    }

    public function getName()
    {
        return 'enum';
    }
}
