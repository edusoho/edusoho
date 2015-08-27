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
      
			cordovaUtil.learnCourseLesson(data.courseId, data.id); 
      window.history.back();
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
      var index = 0;
      var lessonIds = [];
      var lessons = $scope.lessons;
      for (var i = 0; i < lessons.length; i++) {
        if ("lesson" == lessons[i].itemType) {
          lessonIds[index++] = lessons[i].id;
        }
      };

      return lessonIds;
    };

    //lesson.free=1 is free
    $scope.learnLesson = function(lesson) {
      if (! $scope.member && 1 != lesson.free) {
        alert("请先加入学习");
        return;
      }

      if ("lesson" != lesson.itemType) {
        return;
      }

      if (lesson.type == "flash" || "mediaSource" == lesson.mediaSource) {
        alert("客户端暂不支持该课时类型，敬请期待新版");
        return;
      }
      cordovaUtil.learnCourseLesson(lesson.courseId, lesson.id, self.createLessonIds());     
    }

    this.loadLessons();
}