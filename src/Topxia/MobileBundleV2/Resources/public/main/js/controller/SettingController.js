app.controller('SettingController', ['$scope', 'UserService', '$state', SettingController]);

function SettingController($scope, UserService, $state)
{
	$scope.isShowLogoutBtn = $scope.user ? true : false;
	$scope.logout = function() {
		$scope.showLoad();
		UserService.logout( {}, function(data) {
			$scope.hideLoad();
			$state.go("slideView.mainTab");
		});
	}
}