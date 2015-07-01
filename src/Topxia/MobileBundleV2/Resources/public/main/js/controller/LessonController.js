app.controller('LessonController', ['$scope', '$stateParams', 'LessonService', LessonController]);

function LessonController($scope, $stateParams, LessonService)
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
			$scope.lessonView = "view/lesson_" + data.type + ".html";
		});
	}

	this.loadLesson();
}