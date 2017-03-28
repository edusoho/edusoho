<?php

class TagOwnerMigrate extends AbstractMigrate
{
    public function update($page)
    {
        $this->exec(
          "update `tag_owner` set `ownerType` = 'courseSet' where `ownerType`='course';"
        );
    }
}
