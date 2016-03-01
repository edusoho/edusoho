app.controller('LoginController', ['$scope', 'UserService', '$stateParams', 'platformUtil', 'cordovaUtil', '$state', LoginController]);

function LoginController($scope, UserService, $stateParams, platformUtil, cordovaUtil, $state, $q)
{	
	console.log("LoginController");

	$scope.user = {
		username : null,
		password : null
	};

	cordovaUtil.getThirdConfig($q).then(function(data) {
		$scope.thirdConfig = data;
	});

	$scope.jumpToMain = function() {
		$state.go("slideView.mainTab");
	}

	$scope.getThirdStyle = function() {
		if (!$scope.thirdConfig || $scope.thirdConfig.length <= 1) {
			return "";
		}
		return $scope.thirdConfig.length == 2 ? "ui-grid-halve" : "ui-grid-trisect";
	}

	$scope.hasThirdType = function(name) {
		if (! $scope.thirdConfig) {
			return false;
		}

		return $scope.thirdConfig.indexOf(name) != -1;
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

	$scope.loginWithOpen = function(type) {
		cordovaUtil.openPlatformLogin(type);
	}

	$scope.jumpToSetting = function() {
		cordovaUtil.startAppView("setting", {});
	}
}