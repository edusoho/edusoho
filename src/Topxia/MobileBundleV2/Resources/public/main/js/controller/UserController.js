app.controller('MyInfoController', ['$scope', 'UserService', 'cordovaUtil', 'platformUtil', '$stateParams', '$q', MyInfoController]);
app.controller('TeacherListController', ['$scope', 'UserService', 'ClassRoomService', '$stateParams', TeacherListController]);
app.controller('UserInfoController', ['$scope', 'UserService', '$stateParams', 'AppUtil', 'cordovaUtil', UserInfoController]);
app.controller('StudentListController', ['$scope', 'ClassRoomService', 'CourseService', '$stateParams', StudentListController]);

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
			limit : 10000,
			classRoomId : $stateParams.targetId
		}, function(data) {
			$scope.users = data;
		});
	};

	this.loadCourseTeachers = function() {
		UserService.getCourseTeachers({
			limit : 10000,
			courseId : $stateParams.targetId
		}, function(data) {
			$scope.users = data;
		});
	};

	$scope.loadUsers = function() {
		self.targetService();
	}

	$scope.getUserAvatar = function(user) {
		if (user.avatar) {
			return user.avatar;
		}

		if (user.mediumAvatar) {
			return user.mediumAvatar;
		}

		return "";
	}

	this.initService();
}

function StudentListController($scope, ClassRoomService, CourseService, $stateParams)
{
	$scope.title = getTitle($stateParams.targetType);

	function getTitle(targetType) {
		if ("classroom" == $stateParams.targetType) {
			return "班级学员";
		}

		return "课程学员";
	}

	function getEmptyStr(targetType) {
		if ("classroom" == $stateParams.targetType) {
			return "该班级暂无学员";
		}

		return "该课程暂无学员";
	}

	$scope.canLoad = true;
    $scope.start = $scope.start || 0;

	$scope.title = getTitle($stateParams.targetType);
	$scope.emptyStr = getEmptyStr($stateParams.targetType);

	$scope.loadMore = function(successCallback){
        if (! $scope.canLoad) {
            return;
        }
        setTimeout(function() {
        	$scope.loadUsers(successCallback);
        }, 200);
    };

	function getClassRoomStudents(targetId, callback) {
		ClassRoomService.getStudents({
			start : $scope.start,
			classRoomId : $stateParams.targetId
		}, callback);
	}

	function getCourseStudents(targetId, callback) {
		CourseService.getStudents({
			start : $scope.start,
			courseId : $stateParams.targetId,
		}, callback);
	}

	function getStudentArray(resources) {
		var users = [];
		for (var i = 0; i < resources.length; i++) {
			users[i] = resources[i].user;
		};

		return users;
	}

	$scope.loadUsers = function(successCallback) {
		var service;
		if ("classroom" == $stateParams.targetType) {
			service = getClassRoomStudents;
		} else {
			service = getCourseStudents;
		}
		service($stateParams.targetId, function(data) {
			if (successCallback) {
              successCallback();
            }
            var length  = data ? data.resources.length : 0;
            if (length == 0 || length < 10) {
                $scope.canLoad = false;
            }
			var users = getStudentArray(data.resources);
			$scope.users = $scope.users || [];
            for (var i = 0; i < length; i++) {
              $scope.users.push(users[i]);
            };

            $scope.start += 10;
		});
	}

	$scope.getUserAvatar = function(user) {
		if (user.avatar) {
			return user.avatar;
		}

		if (user.mediumAvatar) {
			return user.mediumAvatar;
		}

		return "";
	}
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
		if (userinfo.signature == "") {
			$scope.toast("签名不能为空！");
			return
		}

		if (userinfo.nickname.indexOf(".") != -1 || userinfo.nickname.indexOf("^") != -1) {
			$scope.toast("昵称不能包含.^等特殊字符!");
			return
		}
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

function UserInfoController($scope, UserService, $stateParams, AppUtil, cordovaUtil) 
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

	$scope.isUnOwner = function() {

		if ($scope.user && $scope.user.id == $stateParams.userId) {
			return false;
		}

		return true;
	};

	$scope.loadUserInfo = function() {
		$scope.showLoad();
		UserService.getUserInfo({
			userId : $stateParams.userId
		}, function(data) {
			$scope.hideLoad();
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
	};

	this.follow = function() {
		UserService.follow({
			toId : $stateParams.userId
		}, function(data) {
			if (data && data.toId == $stateParams.userId) {
				$scope.isFollower = true;
				cordovaUtil.sendNativeMessage("refresh_friend_list", {});
			}
		});
	}

	this.unfollow = function() {
		UserService.unfollow({
			toId : $stateParams.userId
		}, function(data) {
			if (data) {
				$scope.isFollower = false;
				cordovaUtil.sendNativeMessage("refresh_friend_list", {});
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

app.controller('TeacherTodoListController', ['$scope', '$stateParams', 'AnalysisService', TeacherTodoListController]);
function TeacherTodoListController($scope, $stateParams, AnalysisService) {

	Chart.defaults.global.tooltipTemplate = "<%= value %>";
	Chart.defaults.global.tooltipEvents = [""];
	Chart.defaults.global.animation = false;
	Chart.defaults.global.tooltipFillColor = "rgba(0,0,0,0)";
	Chart.defaults.global.tooltipFontColor = "#000";
	Chart.defaults.global.scaleLineColor = "rgba(0,0,0,0)";

	var self = this;

	$scope.initChartData = function() {
		$scope.showLoad();
		AnalysisService.getCourseChartData({
			courseId : $stateParams.courseId
		}, function(data) {
			$scope.hideLoad();
			if (data.error) {
				$scope.toast(data.error.message);
				return;
			}
			$scope.charts = data;
		});
	}

	$scope.loadCharts = function() {
		setTimeout(function(){
			for (var i = 0; i < $scope.charts.length; i++) {
				initChart($scope.charts[i], i);
			};
		}, 10);	
	};
	
	function initChart(chartData, id) {
		var ctx = document.getElementById("chart_" + id).getContext("2d");
		var chartLineColor = chartData.chartLineColor || "#37b97d";
		var data = {
		    labels: chartData.labelData,
		    datasets: [
		        {
		            label: "My First dataset",
		            fillColor: "rgba(0, 0, 0, 0)",
		            strokeColor: chartLineColor,
		            pointColor: chartLineColor,
		            pointStrokeColor: "#fff",
		            pointHighlightFill: chartLineColor,
		            pointHighlightStroke: chartLineColor,
		            data: chartData.pointData
		        }
		    ]
		};

		var defaults = {
			scaleShowGridLines : true,
			bezierCurve  : false,
			pointDot : true,
			pointDotRadius : 2
		};

		function showToolTips(lineChart) {
			var activePoints = lineChart.datasets[0].points;
			lineChart.eachPoints(function(point){
				point.restore(['fillColor', 'strokeColor']);
			});
			Chart.helpers.each(activePoints, function(activePoint){
				activePoint.fillColor = activePoint.highlightFill;
				activePoint.strokeColor = activePoint.highlightStroke;
			});
			lineChart.showTooltip(activePoints);
		}
		var myLineChart, chart = new Chart(ctx);
		var render = Chart.types.Line.prototype.render;

		Chart.types.Line.prototype.render = function(reflow) {
			var self = this;
			render.call(this, reflow);
			setTimeout(function() {
				showToolTips(self);
			}, 10);
		};

		myLineChart = chart.Line(data, defaults);
	}
}

app.controller('HomeworkTeachingController', ['$scope', '$stateParams', 'HomeworkManagerService', HomeworkTeachingController]);
function HomeworkTeachingController($scope, $stateParams, HomeworkManagerService) {

	var self = this;

	this.filter = function(data) {
		var users = data.users;
		var homeworkResults = data.homeworkResults;
		for (var i = 0; i < homeworkResults.length; i++) {
			homeworkResults[i]["user"] = users[homeworkResults[i]["userId"]];
		};
		data.homeworkResults = homeworkResults;
		console.log(data);
		return data;
	};

	$scope.showHomeWorkResult = function(homeworkResult) {
		alert("暂不支持在客户端批改作业");
	};

	$scope.initTeachingResult = function() {
		HomeworkManagerService.teachingResult({
			start : 3,
			courseId : $stateParams.courseId
		}, function(data) {
			$scope.teachingResult = self.filter(data);
		});
	};
}

app.controller('ThreadTeachingController', ['$scope', '$stateParams', 'ThreadManagerService', 'cordovaUtil', ThreadTeachingController]);
function ThreadTeachingController($scope, $stateParams, ThreadManagerService, cordovaUtil) {

	var self = this;

	$scope.courseId  =$stateParams.courseId;

	this.filter = function(data) {
		var users = data.users;
		var threads = data.threads;

		for (var i = 0; i < threads.length; i++) {
			threads[i]["user"] = users[threads[i]["userId"]];
		};
		
		data.threads = threads;
		return data;
	};

	$scope.showThreadChatView = function(thread) {
		cordovaUtil.startAppView("threadDiscuss", {
			type : "thread.post",
			courseId : thread.courseId,
			lessonId : thread.lessonId,
			threadId : thread.id
		});
	};

	$scope.initQuestionResult = function(limit) {
		$scope.showLoad();
		ThreadManagerService.questionResult({
			start : limit,
			courseId : $stateParams.courseId
		}, function(data) {
			$scope.hideLoad();
			$scope.teachingResult = self.filter(data);
		});
	};
}