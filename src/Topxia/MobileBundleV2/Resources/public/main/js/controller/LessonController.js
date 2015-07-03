app.controller('LessonController', ['$scope', '$stateParams', 'LessonService', 'cordovaUtil', LessonController]);

function LessonController($scope, $stateParams, LessonService, cordovaUtil)
{	
	var self = this;

	self.loadLesson = function() {
		LessonService.getLesson({
			courseId : $stateParams.courseId,
			lessonId : $stateParams.lessonId,
			token : $scope.token
		},function(data) {
			if (data.error) {
				$scope.toast(data.error.message);
				return;
			}
			$scope.lesson = data;
			if (data.type != "text") {
				cordovaUtil.learnCourseLesson(data.courseId, data.id); 
				window.history.back();
				return;
			}
			$scope.lessonView = "view/lesson_" + data.type + ".html";
		});
	}

	this.loadLesson();
}