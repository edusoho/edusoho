app.controller('LoginController', ['$scope', 'UserService', '$state', '$stateParams', '$window', LoginController]);

function LoginController($scope, UserService, $state, $stateParams, $window)
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
    			
			$scope.hideLoad();
    			if (data.error) {
				$scope.toast(data.error.message);
				return;
			}

			if ($stateParams.goto) {
				$window.history.back();
				setTimeout(function() {
				         $scope.$emit("refresh", {});
				}, 10);
			} else {
				$scope.jumpToMain();
			}
			
    		});
    	}
}