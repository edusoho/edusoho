app.controller('LoginController', ['$scope', 'UserService', '$state', '$ionicHistory', '$stateParams', LoginController]);

function LoginController($scope, UserService, $state, $ionicHistory, $stateParams)
{	
	console.log("LoginController");

	$scope.user = {
		username : null,
		password : null
	};

	$scope.jumpToMain = function() {
		$state.go("slideView.mainTab");
	}

    	$scope.login = function(user) {
    		$scope.showLoad();
    		UserService.login({
    			"_username": user.username,
			"_password" : user.password
    		}, function(data) {
    			if (data.meta.code == 500) {
				$scope.toast(data.meta.message);
				return;
			}
			$scope.hideLoad();
			if ($stateParams.goto) {
				$ionicHistory.goBack();
				setTimeout(function() {
				         $scope.$emit("refresh", {});
				}, 10);
			} else {
				$scope.jumpToMain();
			}
			
    		});
    	}
}