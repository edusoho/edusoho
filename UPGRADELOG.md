8.0 升级日志

废弃的api：

	CourseService:
		countMembersByStartTimeAndEndTime
		findMobileVerifiedMemberCountByCourseId
		deleteMemberByCourseIdAndUserId

	CourseMemberDao:
		getMemberCountByUserIdAndCourseTypeAndIsLearned
		getMemberCountByUserIdAndRoleAndIsLearned
		findMembersByUserIdAndCourseTypeAndIsLearned
		findMembersByUserIdAndRoleAndIsLearned
		findStudentsByCourseId
		findTeachersByCourseId
		findMemberCountByCourseIdAndRole
		
    MaterialService:
        findMaterialCountGroupByFileId
        findMaterialsGroupByFileId
        findLessonMaterials
        getMaterialCount
    