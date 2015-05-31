app.controller('UserInfoController', ['$scope', 'httpService', '$ionicLoading', '$stateParams', UserInfoController]);
app.controller('TeacherListController', ['$scope', 'UserService', '$stateParams', TeacherListController]);

function TeacherListController($scope, UserService, $stateParams)
{
	UserService.getCourseTeachers({
		courseId : $stateParams.courseId
	}, function(data) {
		$scope.users = data;
		console.log(data);
	});
}

function UserInfoController($scope, httpService, $ionicLoading, $stateParams) 
{
	$ionicLoading.show({
	        template:'加载中...',
	});

	httpService.get({
		url : app.host + "/mapi_v2/User/getUserInfo",
		params : {
			userId : $scope.user.id
		},
		success : function(data) {
			$scope.user = data;
			$ionicLoading.hide();
		},
		error : function(data) {
			$ionicLoading.hide();
		}
	});
}