<?php

class ClassroomCourseMigrate extends AbstractMigrate
{
    public function update($page)
    {
        $countSql = 'SELECT count(c2.id)  FROM `classroom_courses` c2   LEFT JOIN  `course_v8` c1  ON c1.id = c2.`courseId`';
        $count = $this->getConnection()->fetchColumn($countSql);
        if ($count == 0) {
            return;
        }
        $this->perPageCount = 100000;
        $start = $this->getStart($page);

        $this->exec("
          UPDATE
          	`course_v8` c1, `classroom_courses` c2, classroom c3
          SET
          	c1.`expiryMode` = c3.`expiryMode`,
          	c1.`expiryDays` = c3.`expiryValue`
          WHERE
          	c1.id = c2.`courseId` AND c3.id = c2.`classroomId` AND c1.`parentId` <> 0 AND  c3.`expiryMode` IN ( 'days', 'forever') AND c1.id
          	IN
          	( SELECT courseId FROM
          		( SELECT  courseId  FROM `classroom_courses` c2   LEFT JOIN  `course_v8` c1  ON c1.id = c2.`courseId` order by c2.id limit {$start}, {$this->perPageCount} )
          	AS c4);
        ");


        $this->exec("
          UPDATE
          	`course_v8` c1, `classroom_courses` c2, classroom c3
          SET
          	c1.`expiryMode` = c3.`expiryMode`,
          	c1.`expiryEndDate` = c3.`expiryValue`
          WHERE
          	c1.id = c2.`courseId` AND c3.id = c2.`classroomId` AND c1.`parentId` <> 0 AND  c3.`expiryMode` = 'date' AND c1.id
          	IN
          	( SELECT courseId FROM
          		( SELECT  courseId  FROM `classroom_courses` c2   LEFT JOIN  `course_v8` c1  ON c1.id = c2.`courseId` order by c2.id limit {$start}, {$this->perPageCount} )
          	AS c4);
        ");

        $nextPage = $this->getNextPage($count, $page);
        if (empty($nextPage)) {
            return;
        }

        return $nextPage;
    }
}
