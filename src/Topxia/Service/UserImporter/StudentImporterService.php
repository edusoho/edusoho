<?php
namespace Topxia\Service\UserImporter;


interface StudentImporterService
{
	public function importStudentByUpdate($students);

	public function importStudentByIgnore($students, $classId);

	public function checkStudentData($file, $rule, $classId);
}
