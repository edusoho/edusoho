app.controller('MyInfoController', ['$scope', 'httpService', '$stateParams', MyInfoController]);
app.controller('TeacherListController', ['$scope', 'UserService', 'ClassRoomService', '$stateParams', TeacherListController]);
app.controller('UserInfoController', ['$scope', 'UserService', '$stateParams', 'AppUtil', UserInfoController]);
app.controller('StudentListController', ['$scope', 'ClassRoomService', '$stateParams', StudentListController]);

function TeacherListController($scope, UserService, ClassRoomService, $stateParams)
{
	$scope.title = "课程教师";
	var self = this;
	this.initService = function() {
		if ("course" == $stateParams.targetType) {
			self.targetService = self.loadCourseTeachers;
		} else if ("classroom" == $stateParams.targetType) {
			$scope.title = "班级教师";
			self.targetService = self.loadClassRoomTeachers;
		}
	};

	this.loadClassRoomTeachers = function() {
		ClassRoomService.getTeachers({
			classRoomId : $stateParams.targetId
		}, function(data) {
			$scope.users = data;
		});
	};

	this.loadCourseTeachers = function() {
		UserService.getCourseTeachers({
			courseId : $stateParams.targetId
		}, function(data) {
			$scope.users = data;
		});
	};

	$scope.loadUsers = function() {
		self.targetService();
	}

	this.initService();
}

function StudentListController($scope, ClassRoomService, $stateParams)
{
	$scope.title = "班级学员";
	ClassRoomService.getStudents({
		classRoomId : $stateParams.targetId,
		limit : -1
	}, function(data) {
		$scope.users = data.data;
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
				$scope.isFollower = (true == data || "true" == data) ? true : false;
				console.log($scope.isFollower);
			});
		}
	});

	this.follow = function() {
		UserService.follow({
			toId : $stateParams.userId
		}, function(data) {
			if (data && data.toId == $stateParams.userId) {
				$scope.isFollower = true;
			}
		});
	}

	this.unfollow = function() {
		UserService.unfollow({
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