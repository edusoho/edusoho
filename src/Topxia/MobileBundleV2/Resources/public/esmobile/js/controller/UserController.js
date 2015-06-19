app.controller('MyInfoController', ['$scope', 'httpService', '$stateParams', MyInfoController]);
app.controller('TeacherListController', ['$scope', 'UserService', '$stateParams', TeacherListController]);
app.controller('UserInfoController', ['$scope', 'UserService', '$stateParams', 'AppUtil', UserInfoController]);

function TeacherListController($scope, UserService, $stateParams)
{
	UserService.getCourseTeachers({
		courseId : $stateParams.courseId
	}, function(data) {
		$scope.users = data;
	});
}

function MyInfoController($scope, httpService, $stateParams) 
{
	httpService.get({
		url : app.host + "/mapi_v2/User/getUserInfo",
		params : {
			userId : $scope.user.id
		},
		success : function(data) {
			$scope.userinfo = data;
		},
		error : function(data) {
			$ionicLoading.hide();
		}
	});
}

function UserInfoController($scope, UserService, $stateParams, AppUtil) 
{
	var self = this;

	$scope.isFollower = null;
	$scope.uiBarTransparent = true;

	$scope.changeTabStatus = function(headTop, scrollTop) {
		var transparent = scrollTop < headTop;
		if (transparent == $scope.uiBarTransparent) {
			return;
		}

		$scope.$apply(function() {
			$scope.uiBarTransparent = transparent;
		});
	}

	this.isTeacher = function(role) {
		return AppUtil.inArray('ROLE_TEACHER',role) > 0;
	}

	this.getUserLearnCourse = function() {
		UserService.getLearningCourseWithoutToken({
			userId : $stateParams.userId
		}, function(data) {
			$scope.courses = data.data;
		});
	}

	this.getUserTeachCourse = function() {
		UserService.getUserTeachCourse({
			userId : $stateParams.userId
		}, function(data) {
			$scope.courses = data.data;
		});
	}

	UserService.getUserInfo({
		userId : $stateParams.userId
	}, function(data) {
		if (! data) {
			$scope.toast("获取用户信息失败！");
			return;
		}
		$scope.userinfo = data;
		$scope.isTeacher = self.isTeacher(data.roles);
		if ($scope.isTeacher) {
			self.getUserTeachCourse();
		} else {
			self.getUserLearnCourse();
		}

		if ($scope.user) {
			UserService.searchUserIsFollowed({
				userId : $scope.user.id,
				toId : $stateParams.userId
			}, function(data) {
				$scope.isFollower = true == data ? true : false;
			});
		}
	});

	this.follow = function() {
		UserService.follow({
			token : $scope.token,
			toId : $stateParams.userId
		}, function(data) {
			if (data && data.toId == $stateParams.userId) {
				$scope.isFollower = true;
			}
		});
	}

	this.unfollow = function() {
		UserService.unfollow({
			token : $scope.token,
			toId : $stateParams.userId
		}, function(data) {
			if (data) {
				$scope.isFollower = false;
			}
		});
	}

	$scope.changeFollowUser = function() {
		if (true == $scope.isFollower) {
			self.unfollow();
		} else {
			self.follow();
		}
		
	}

	

}