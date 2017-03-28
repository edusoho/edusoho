<?php

class CourseLessonView2CourseTaskView extends AbstractMigrate
{
    public function update($page)
    {
    	if (!$this->isTableExist('course_task_view')) {
            $this->exec(
            "
                CREATE TABLE `course_task_view` (
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                  `courseSetId` int(10) NOT NULL,
                  `courseId` int(10) NOT NULL,
                  `taskId` int(10) NOT NULL,
                  `fileId` int(10) NOT NULL,
                  `userId` int(10) NOT NULL,
                  `fileType` varchar(80) NOT NULL,
                  `fileStorage` varchar(80) NOT NULL,
                  `fileSource` varchar(32) NOT NULL,
                  `createdTime` int(10) unsigned NOT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            "
            );
        }

        $countSql = 'SELECT count(*) FROM `course_lesson_view` where `id` not in (select `id` from `course_task_view`)';
        $count = $this->getConnection()->fetchColumn($countSql);
        if ($count == 0) {
			return;
        }

        $this->exec(
            "
            INSERT INTO `course_task_view`
            (
                `id`,
                `courseSetId`,
                `courseId`,
                `taskId`,
                `fileId`,
                `userId`,
                `fileType`,
                `fileStorage`,
                `fileSource`,
                `createdTime`
            )
            SELECT
                `id`,
                `courseId`,
                `courseId`,
                `lessonId`,
                `fileId`,
                `userId`,
                `fileType`,
                `fileStorage`,
                `fileSource`,
                `createdTime`
            FROM `course_lesson_view` WHERE id not in (SELECT id FROM `course_task_view`)
            order by id limit 0, {$this->perPageCount};
        	"
        );
        
        return $page+1;
    }
}
