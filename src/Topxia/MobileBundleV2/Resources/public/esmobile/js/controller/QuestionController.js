app.controller('QuestionController', ['$scope', 'QuestionService', '$stateParams', '$ionicLoading', QuestionController]);

function QuestionController($scope, QuestionService, $stateParams, $ionicLoading)
{
	$ionicLoading.show({
	        template:'加载中...',
	});

	QuestionService.getThread({
		courseId: $stateParams.courseId,
		threadId : $stateParams.threadId,
		token : $scope.token
	}, function(data) {
		$scope.thread = data;
		$ionicLoading.hide();
	});

	$scope.loadTeacherPost = function() {
		QuestionService.getThreadTeacherPost({
			courseId: $stateParams.courseId,
			threadId : $stateParams.threadId,
			token : $scope.token
		}, function(data) {
			$scope.teacherPosts = data;
		});
	}

	$scope.loadTheadPost = function() {
		QuestionService.getThreadPost({
			courseId: $stateParams.courseId,
			threadId : $stateParams.threadId,
			token : $scope.token
		}, function(data) {
			$scope.threadPosts = data.data;
		});
	}
}