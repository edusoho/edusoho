app.controller('RegistController', ['$scope', 'platformUtil', 'UserService', '$state', RegistController]);

function RegistController($scope, platformUtil, UserService, $state)
{
	console.log("RegistController");

	$scope.user = {
		phone : null,
		code : null,
		password: null
	};

	var self = this;

	this.registHandler = function(params) {
		$scope.showLoad();
		UserService.regist(params, function(data) {
			$scope.hideLoad();
			if (data.error) {
				$scope.toast(data.error.message);
				return;
			}
			if (platformUtil.native) {
				esNativeCore.closeWebView();
				return;
			}
			self.jumpToMain();
		}, function(error) {
			$scope.toast("注册失败");
		});
	}

	this.jumpToMain = function() {
		$state.go("slideView.mainTab");
	}

	$scope.checkCode = function(code) {
		return !code || code.length == 0;
	};

	$scope.sendSmsCode = function(phone) {
		if (!parseInt(phone)) {
			alert("手机格式不正确!");
			return;
		}

		UserService.smsSend({
			phoneNumber : phone
		}, function(data) {
			$scope.toast(data.error.message);
		});
	}

	$scope.registWithPhone = function(user) {
		if (!parseInt(user.phone)) {
			alert("手机格式不正确!");
			return;
		}
		if (user.password && (user.password.length < 5 || user.password.length > 20)) {
			alert("密码格式不正确!");
			return;
		}

		if ($scope.checkCode(user.code)) {
			alert("验证码不正确!");
			return;
		}
		self.registHandler({
			phone : user.phone,
			smsCode : user.code,
			password : user.password,
		});
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
		self.registHandler({
			email : user.email,
			password : user.password,
		});
	}
}