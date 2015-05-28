app.controller('UserInfoController', ['$scope', 'httpService', '$ionicLoading', '$stateParams', UserInfoController]);
app.controller('TeacherListController', ['$scope', 'CourseService', '$stateParams', TeacherListController]);

function TeacherListController($scope, CourseService, $stateParams)
{
	
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