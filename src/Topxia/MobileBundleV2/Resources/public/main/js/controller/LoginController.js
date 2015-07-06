app.controller('LoginController', ['$scope', 'UserService', '$state', '$stateParams', 'platformUtil', LoginController]);

function LoginController($scope, UserService, $state, $stateParams, platformUtil)
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

			if (platformUtil.native) {
				esNativeCore.closeWebView();
				return;
			}

			if ($stateParams.goto) {
				window.history.back();
			} else {
				$scope.jumpToMain();
			}
			
    		});
    	}
}