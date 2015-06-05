app.controller('RegistController', ['$scope', '$http', '$ionicHistory', 'UserService', RegistController]);

function RegistController($scope, $http, $ionicHistory, UserService)
{
	console.log("RegistController");

	$scope.user = {
		phone : null,
		code : null,
		password: null
	};

	$scope.checkCode = function(code) {
		return false;
	};

	$scope.checkEmail = function(code) {
		return false;
	};

	$scope.registWithPhone = function(user) {
		if (!parseInt(user.phone)) {
			alert("手机格式不正确!");
			return;
		}
		if (user.password && (user.password.length < 5 || user.password.length > 20)) {
			alert("密码格式不正确!");
			return;
		}

		if (!$scope.checkCode(user.code)) {
			alert("验证码不正确!");
			return;
		}
	}

	$scope.registWithEmail = function(user) {
		if (!user.email) {
			alert("邮箱格式不正确!");
			return;
		}
		if (user.password && (user.password.length < 5 || user.password.length > 20)) {
			alert("密码格式不正确!");
			return;
		}

		UserService.regist({
			email : user.email,
			password : user.password,
		}, function(data) {
			if (data.meta.code == 500) {
				$scope.toast(data.meta.message);
				return;
			}
			$ionicLoading.hide();
			$scope.jumpToMain();
		}, function(error) {

		});
	}
}