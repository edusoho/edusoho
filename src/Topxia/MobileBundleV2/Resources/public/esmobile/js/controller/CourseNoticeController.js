app.controller('CourseNoticeController', ['$scope', 'CourseService', '$stateParams', CourseNoticeController]);

function CourseNoticeController($scope, CourseService, $stateParams)
{
	var limit = 10;
	$scope.start = 0;
	$scope.showLoadMore = true;
	
	function loadNotices(start, limit) {
		CourseService.getCourseNotices({
			start : $scope.start,
			limit : limit,
			courseId : $stateParams.courseId
		}, function(data) {
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

	loadNotices($scope.start, limit);
}