app.controller('MyInfoController', ['$scope', 'UserService', 'cordovaUtil', 'platformUtil', '$stateParams', '$q', MyInfoController]);
app.controller('TeacherListController', ['$scope', 'UserService', 'ClassRoomService', '$stateParams', TeacherListController]);
app.controller('UserInfoController', ['$scope', 'UserService', '$stateParams', 'AppUtil', UserInfoController]);
app.controller('StudentListController', ['$scope', 'ClassRoomService', '$stateParams', StudentListController]);

function TeacherListController($scope, UserService, ClassRoomService, $stateParams)
{
	$scope.title = "课程教师";
	$scope.emptyStr = "该课程暂无教师";
	var self = this;
	this.initService = function() {
		if ("course" == $stateParams.targetType) {
			self.targetService = self.loadCourseTeachers;
		} else if ("classroom" == $stateParams.targetType) {
			$scope.title = "班级教师";
			$scope.emptyStr = "该班级暂无教师";
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
	$scope.emptyStr = "该班级暂无学员";
	ClassRoomService.getStudents({
		classRoomId : $stateParams.targetId,
		limit : -1
	}, function(data) {
		$scope.users = data.data;
	});
}

function MyInfoController($scope, UserService, cordovaUtil, platformUtil, $stateParams, $q) 
{	
	var self = this;
	this.uploadAvatar = function(file) {
		$scope.showLoad();
		UserService.uploadAvatar({
			file : file.files[0]
		}, function(data) {
			$scope.hideLoad();
			if (data.error) {
				$scope.toast(data.error.message);
				return;
			}
			$scope.userinfo.fileId = data.id;
			$scope.userinfo.mediumAvatar = data.url;
		});
	};

	$scope.showSelectImage = function(e) {
		if (platformUtil.native && platformUtil.android) {
			e.preventDefault();
			cordovaUtil.uploadImage(
				$q,
				app.host + '/mapi_v2/User/uploadAvatar',
				{ token : $scope.token },
				{ file : "" },
				"image/*"
			).then(function(data) {
				if (! data) {
					alert("该功能仅支持客户端!");
					return;
				}
				$scope.userinfo.fileId = data.id;
				$scope.userinfo.mediumAvatar = data.url;
			});
		}
	};

	$scope.loadUserInfo = function() {
		$scope.showLoad();
		UserService.getUserInfo({
			userId : $scope.user.id
		}, function(data) {
			$scope.userinfo = data;
			$scope.hideLoad();
		});
	};

	$scope.generArray = ['female', 'male'];

	$scope.updateUserProfile = function() {
		var userinfo = $scope.userinfo;
		var params = {
			'fileId' : userinfo.fileId,
			'profile[nickname]' : userinfo.nickname,
			'profile[gender]' : userinfo.gender,
			'profile[signature]' : userinfo.signature
		};
		$scope.showLoad();
		UserService.updateUserProfile(params, function(data) {
			if (data.error) {
				$scope.toast(data.error.message);
				return;
			}
			$scope.toast("更新成功!");
			cordovaUtil.updateUser(data);
			$scope.hideLoad();
		});
	};

	$scope.uploadChange = function(file) {
		if (file && file.value) {
			self.uploadAvatar(file);
		}
	}
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