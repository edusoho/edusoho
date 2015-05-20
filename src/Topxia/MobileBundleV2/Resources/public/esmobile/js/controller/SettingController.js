app.controller('SettingController', ['$scope', '$ionicLoading', 'UserService', '$state', SettingController]);

function SettingController($scope, $ionicLoading, UserService, $state)
{
	$scope.isShowLogoutBtn = $scope.user ? true : false;
	$scope.logout = function() {
		$ionicLoading.show({
		        template:'加载中...',
		});
		UserService.logout({
			token : $scope
		}, function(data) {
			$ionicLoading.hide();
			$state.go("slideView.mainTab");
		});
	}
}