app.controller('LoginController', ['$scope', 'UserService', '$state', '$ionicLoading', LoginController]);

function LoginController($scope, UserService, $state, $ionicLoading)
{	
	var _super = new baseController($scope);
	this.__proto__ = _super;

	console.log("LoginController");

	var localStore = this.getService("localStore");
	$scope.user = {
		username : null,
		password : null
	};

	$scope.jumpToMain = function() {
		$state.go("slideView.mainTab");
	}

    	$scope.login = function(user) {
    		$ionicLoading.show({
		        template:'加载中...',
		});
    		UserService.login({
    			"_username": user.username,
			"_password" : user.password
    		}, function(data) {
			$ionicLoading.hide();
			$scope.jumpToMain();
    		});
    	}
}