app.controller('CourseNoticeController', ['$scope', 'CourseService', 'ClassRoomService', '$stateParams', CourseNoticeController]);

function CourseNoticeController($scope, CourseService, ClassRoomService, $stateParams)
{
	var limit = 10;
	$scope.start = 0;
	$scope.showLoadMore = true;
	
	this.loadCourseNotices = function(callback) {
	    CourseService.getCourseNotices({
	      start : $scope.start,
	      limit : limit,
	      courseId : $stateParams.targetId
	    }, callback);
	};

  	this.loadClassRoomNotices = function(callback) {
	    ClassRoomService.getAnnouncements({
	      start : $scope.start,
	      limit : 10,
	      classRoomId : $stateParams.targetId
	    }, callback);
	};

  	this.initTargetService = function(targetType) {
	    if (targetType == "course") {
	    	$scope.titleType = "课程";
	      	self.targetService = this.loadCourseNotices;
	    } else if (targetType == "classroom") {
	    	$scope.titleType = "班级";
	      	self.targetService = this.loadClassRoomNotices;
	    }
	};


	function loadNotices(start, limit) {
		self.targetService(function(data) {
			$scope.notices = $scope.notices || [];
			
			if (! data || data.length == 0) {
				$scope.showLoadMore = false;
				$scope.toast("没有更多消息");
				return;
			}
			
			for (var i = 0; i < data.length; i++) {
		                  $scope.notices.push(data[i]);
		           };
			$scope.start += limit;
		});
	}

	$scope.loadMore = function() {
		loadNotices($scope.start, limit);
	}

	this.initTargetService($stateParams.targetType);
}