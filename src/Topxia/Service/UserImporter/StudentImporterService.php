<?php
namespace Topxia\Service\UserImporter;


interface StudentImporterService
{
	public function importStudentByOverwrite();

	public function importStudentByIgnoreError();

	public function checkStudentData($file);
}
