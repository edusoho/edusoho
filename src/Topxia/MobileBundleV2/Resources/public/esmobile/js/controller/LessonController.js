app.controller('LessonController', ['$scope', '$stateParams', 'LessonService', LessonController]);

function LessonController($scope, $stateParams, LessonService)
{	
	var self = this;

	self.loadLesson = function() {
		LessonService.getLesson({
			courseId : $stateParams.courseId,
			lessonId : $stateParams.lessonId
		},function(data) {
			$scope.lesson = data;
			$scope.lessonView = "view/lesson_" + data.type + ".html";
		});
	}

	this.loadLesson();
}