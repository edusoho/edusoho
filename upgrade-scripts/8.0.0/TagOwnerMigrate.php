<?php

class TagOwnerMigrate extends AbstractMigrate
{
    public function update($page)
    {
        $this->exec(
          "update `tag_owner` set `ownerType` = 'course-set' where `ownerType`='course';"
        );
    }
}
