app.controller('LoginController', ['$scope', 'UserService', '$state', '$stateParams', LoginController]);

function LoginController($scope, UserService, $state, $stateParams)
{	
	console.log("LoginController");

	$scope.user = {
		username : null,
		password : null
	};

	$scope.jumpToMain = function() {
		$state.go("slideView.mainTab.found.course");
	}

    	$scope.login = function(user) {
    		$scope.showLoad();
    		UserService.login({
    			"_username": user.username,
			"_password" : user.password
    		}, function(data) {
    			
			$scope.hideLoad();
    			if (data.meta.code == 500) {
				$scope.toast(data.meta.message);
				return;
			}

			if ($stateParams.goto) {

				setTimeout(function() {
				         $scope.$emit("refresh", {});
				}, 10);
			} else {
				$scope.jumpToMain();
			}
			
    		});
    	}
}