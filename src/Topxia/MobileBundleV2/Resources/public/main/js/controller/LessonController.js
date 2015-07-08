app.controller('LessonController', ['$scope', '$stateParams', 'LessonService', 'cordovaUtil', LessonController]);

function LessonController($scope, $stateParams, LessonService, cordovaUtil)
{	
	var self = this;

	self.loadLesson = function() {
		LessonService.getLesson({
			courseId : $stateParams.courseId,
			lessonId : $stateParams.lessonId
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

app.controller('CourseLessonController', ['$scope', '$stateParams', 'ServcieUtil', '$state', 'cordovaUtil', CourseLessonController]);
function CourseLessonController($scope, $stateParams, ServcieUtil, $state, cordovaUtil)
{
  var LessonService = ServcieUtil.getService("LessonService");
  $scope.loading = true;
  this.loadLessons = function() {
      LessonService.getCourseLessons({
        courseId : $stateParams.courseId,
        token : $scope.token
      }, function(data) {
        $scope.loading = false;
        $scope.$apply(function() {
          $scope.lessons = data.lessons;
          $scope.learnStatuses = data.learnStatuses;

          for( index in data.learnStatuses ) {
            $scope.lastLearnStatusIndex = index;
          }
        });
      });
    }

    $scope.learnLesson = function(lesson) {
      if (! $scope.member && 1 != lesson.free) {
        alert("请先加入学习");
        return;
      }

      if ("text" == lesson.type) {
        $state.go("lesson",  { courseId : lesson.courseId, lessonId : lesson.id } );
        return;
      }

      cordovaUtil.learnCourseLesson(lesson.courseId, lesson.id);     
    }

    this.loadLessons();
}