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

app.controller('CourseLessonController', ['$scope', '$stateParams', 'LessonService', '$state', 'cordovaUtil', CourseLessonController]);
function CourseLessonController($scope, $stateParams, LessonService, $state, cordovaUtil)
{

  var self = this;
  $scope.loading = true;
  this.loadLessons = function() {
      LessonService.getCourseLessons({
        courseId : $stateParams.courseId,
        token : $scope.token
      }, function(data) {
        $scope.loading = false;
        $scope.lessons = data.lessons;
        $scope.learnStatuses = data.learnStatuses;

        for( index in data.learnStatuses ) {
            $scope.lastLearnStatusIndex = index;
        }
      });
    }

    this.createLessonIds = function() {
      var lessonIds = [];
      var lessons = $scope.lessons;
      for (var i = 0; i < lessons.length; i++) {
        if ("lesson" == lessons[i].itemType) {
          lessonIds[i] = lessons[i].id;
        }
      };

      return lessonIds;
    };

    $scope.learnLesson = function(lesson) {
      if (! $scope.member && 1 != lesson.free) {
        alert("请先加入学习");
        return;
      }

      if ("lesson" != lesson.itemType) {
        return;
      }
      
      if ("text" == lesson.type) {
        $state.go("lesson",  { courseId : lesson.courseId, lessonId : lesson.id } );
        return;
      }

      cordovaUtil.learnCourseLesson(lesson.courseId, lesson.id, self.createLessonIds());     
    }

    this.loadLessons();
}