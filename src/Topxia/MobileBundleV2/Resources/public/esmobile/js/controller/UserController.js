app.controller('UserInfoController', ['$scope', 'httpService', '$stateParams', UserInfoController]);
app.controller('TeacherListController', ['$scope', 'UserService', '$stateParams', TeacherListController]);

function TeacherListController($scope, UserService, $stateParams)
{
	UserService.getCourseTeachers({
		courseId : $stateParams.courseId
	}, function(data) {
		$scope.users = data;
	});
}

function UserInfoController($scope, httpService, $stateParams) 
{
	httpService.get({
		url : app.host + "/mapi_v2/User/getUserInfo",
		params : {
			userId : $scope.user.id
		},
		success : function(data) {
			$scope.user = data;
		},
		error : function(data) {
			$ionicLoading.hide();
		}
	});
}