
app.controller('MainController', ['$scope', '$ionicModal', 'sideDelegate', '$state', MainTabController]);

app.controller('FoundTabController', ['$scope', 'CategoryService', '$state', FoundTabController]);
app.controller('MessageTabController', ['$scope', FoundTabController]);
app.controller('ContactTabController', ['$scope', ContactTabController]);

app.controller('FoundCourseController', ['$scope', 'SchoolService', FoundCourseController]);
app.controller('FoundLiveController', ['$scope', 'SchoolService', 'CourseService', FoundLiveController]);
app.controller('FoundClassRoomController', ['$scope', '$http', FoundClassRoomController]);


app.controller('LoginController', ['$scope', 'UserService', '$state', '$ionicLoading', LoginController]);
app.controller('RegistController', ['$scope', '$http', '$ionicHistory', RegistController]);

app.controller('AppInitController', ['$scope', '$ionicHistory', '$state', 'sideDelegate', AppInitController]);
app.controller('CourseListController', ['$scope', '$stateParams', 'AppUtil', 'httpService', CourseListController]);

app.controller('UserInfoController', ['$scope', 'httpService', '$ionicLoading', '$stateParams', UserInfoController]);

app.controller('MyLearnCourseController', ['$scope', 'CourseService', MyLearnCourseController]);
app.controller('MyLearnLiveController', ['$scope',  'CourseService', MyLearnLiveController]);
app.controller('MyLearnClassRoomController', ['$scope', 'CourseService', MyLearnClassRoomController]);

app.controller('MyFavoriteController', ['$scope', 'httpService', MyFavoriteController]);

app.controller('MyGroupQuestionController', ['$scope', 'QuestionService', MyGroupQuestionController]);
app.controller('MyGroupNoteController', ['$scope', 'NoteService', MyGroupNoteController]);

app.controller('QuestionController', ['$scope', 'QuestionService', '$stateParams', '$ionicLoading', QuestionController]);
app.controller('SettingController', ['$scope', '$ionicLoading', 'UserService', '$state', SettingController]);

function SettingController($scope, $ionicLoading, UserService, $state)
{
	$scope.isShowLogoutBtn = $scope.user ? true : false;
	$scope.logout = function() {
		$ionicLoading.show({
		        template:'加载中...',
		});
		UserService.logout({
			token : $scope
		}, function(data) {
			$ionicLoading.hide();
			$state.go("slideView.mainTab");
		});
	}
}

function QuestionController($scope, QuestionService, $stateParams, $ionicLoading)
{
	$ionicLoading.show({
	        template:'加载中...',
	});

	QuestionService.getThread({
		courseId: $stateParams.courseId,
		threadId : $stateParams.threadId,
		token : $scope.token
	}, function(data) {
		$scope.thread = data;
		$ionicLoading.hide();
	});

	$scope.loadTeacherPost = function() {
		QuestionService.getThreadTeacherPost({
			courseId: $stateParams.courseId,
			threadId : $stateParams.threadId,
			token : $scope.token
		}, function(data) {
			$scope.teacherPosts = data;
		});
	}

	$scope.loadTheadPost = function() {
		QuestionService.getThreadPost({
			courseId: $stateParams.courseId,
			threadId : $stateParams.threadId,
			token : $scope.token
		}, function(data) {
			$scope.threadPosts = data.data;
		});
	}
}

function MyGroupNoteController($scope, NoteService)
{
	console.log("MyGroupNoteController");
	$scope.notes = [];
	$scope.canLoad = true;
	$scope.start = $scope.start || 0;
	
  	$scope.loadDataList = function(type) {
  		NoteService.getNoteList({
  			limit : 10,
			start: $scope.start,
			token : $scope.token
  		}, function(data) {
  			if (!data || data.length == 0 ) {
	    			$scope.canLoad = false;
	    		}

	    		$scope.notes = $scope.notes.concat(data);
	    		$scope.start += 10;

	    		$scope.$broadcast('scroll.infiniteScrollComplete');
  		});
  	}

  	$scope.canLoadMore = function() {
  		return $scope.canLoad;
  	};

  	$scope.loadMore = function(type){
  		$scope.loadDataList(type);
  	};
}

function MyGroupQuestionController($scope, QuestionService)
{
	console.log("MyGroupQuestionController");
	$scope.threads = [];
	$scope.canLoad = true;
	$scope.start = $scope.start || 0;
	
  	$scope.loadDataList = function(type) {
  		QuestionService.getCourseThreads({
  			limit : 10,
			start: $scope.start,
			type : type,
			token : $scope.token
  		}, function(data) {
  			if (!data || data.threads.length == 0 ) {
	    			$scope.canLoad = false;
	    		}

	    		$scope.threads = $scope.threads.concat(data.threads);
	    		$scope.start += data.limit;

	    		$scope.$broadcast('scroll.infiniteScrollComplete');
  		});
  	}

  	$scope.canLoadMore = function() {
  		return $scope.canLoad;
  	};

  	$scope.loadMore = function(type){
  		$scope.loadDataList(type);
  	};
}

function MyFavoriteController($scope, CourseService, CourseUtil)
{
	console.log("MyFavoriteController");
	$scope.data  = CourseUtil.getFavoriteListTypes();

  	$scope.loadDataList = function(type) {
  		var dataList = $scope.data[type];
  		CourseService.getFavoriteCoruse(
  			dataList.url,
  			{
	  			limit : 10,
				start: dataList.start,
				token : $scope.token
			}, function(data) {
	  			if (!data || data.data.length == 0) {
		    			dataList.canLoad = false;
		    		}

		    		dataList.data = dataList.data.concat(data.data);
		    		dataList.start += data.limit;

		    		if (data.total && dataList.start >= data.total) {
		    			dataList.canLoad = false;
		    		}
		    		$scope.$broadcast('scroll.infiniteScrollComplete');
  			}
  		);
  	}

  	$scope.canLoadMore = function(type) {
  		return $scope.data[type].canLoad;
  	};

  	$scope.loadMore = function(type){
  		$scope.loadDataList(type);
  	};
}

function MyLearnBaseController($scope, serviceCallback)
{
	$scope.content = {
		start : 0,
		canLoad : true,
		data : []
	};

  	$scope.loadDataList = function(type) {
  		serviceCallback({
  			limit : 10,
			start: $scope.content.start,
			token : $scope.token
  		}, function(data) {
  			console.log(data);
  			if (!data || data.data.length == 0) {
	    			$scope.content.canLoad = false;
	    		}

	    		$scope.content.data = $scope.content.data.concat(data.data);
	    		$scope.content.start += data.limit;

	    		if (data.total && $scope.content.start >= data.total) {
	    			$scope.content.canLoad = false;
	    		}
	    		$scope.$broadcast('scroll.infiniteScrollComplete');
  		});
  	}

  	$scope.canLoadMore = function(type) {
  		return $scope.content.canLoad;
  	};

  	$scope.loadMore = function(type){
  		$scope.loadDataList(type);
  	};
}

function MyLearnCourseController($scope, CourseService)
{
	console.log("MyLearnCourseController");
	this.__proto__  = new MyLearnBaseController($scope, CourseService.getLearningCourse);
}

function MyLearnLiveController($scope, CourseService)
{
	console.log("MyLearnLiveController");
	this.__proto__  = new MyLearnBaseController($scope, CourseService.getLiveCourses);
}

function MyLearnClassRoomController($scope, ClassRoomService)
{
	console.log("MyLearnClassRoomController");
	this.__proto__  = new MyLearnBaseController($scope, ClassRoomService.myClassRooms);	
}

function UserInfoController($scope, httpService, $ionicLoading, $stateParams) 
{
	$ionicLoading.show({
	        template:'加载中...',
	});

	httpService.get({
		url : app.host + "/mapi_v2/User/getUserInfo",
		params : {
			userId : $scope.user.id
		},
		success : function(data) {
			$scope.user = data;
			$ionicLoading.hide();
		},
		error : function(data) {
			$ionicLoading.hide();
		}
	});
}

function baseController($scope)
{
	this.getService = function(name) {
		return angular.injector(["AppService", "ng"]).get(name);
	}
}

function AppInitController($scope, $ionicHistory, $state, sideDelegate)
{	
	var _super = new baseController($scope);
	this.__proto__ = _super;

	$scope.toggle = function() {
	    sideDelegate.toggleMenu();
	};

	$scope.showMyView = function(state) {
		if ($scope.user) {
			$state.go(state);
		} else {
			$state.go("login");
		}
	}
}

function RegistController($scope, $http, $ionicHistory)
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
	}
}

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

function CourseListController($scope, $stateParams, AppUtil, CourseUtil, CourseService, CategoryService, $ionicPopover)
{
	$scope.courses = [];
	$scope.canLoad = true;
	$scope.start = $scope.start || 0;

	console.log("CourseListController");
	$scope.canLoadMore = function() {
  		return $scope.canLoad;
  	};

  	$scope.loadMore = function(){
  		$scope.loadCourseList($stateParams.sort);
  	};

  	$scope.loadCourseList = function(sort) {
  		CourseService.getCourses({
  			limit : 10,
			start: $scope.start,
			categoryId : $stateParams.categoryId,
			sort : sort
  		}, function(data) {
  			if (!data || data.data.length == 0 ) {
	    			$scope.canLoad = false;
	    		}

	    		$scope.courses = $scope.courses.concat(data.data);
	    		$scope.start += data.limit;

	    		$scope.$broadcast('scroll.infiniteScrollComplete');
  		});
  	}

  	$scope.courseListSorts = CourseUtil.getCourseListSorts();
  	$scope.courseListTypes = CourseUtil.getCourseListTypes();


  	CategoryService.getCategorieTree(function(data) {
		$scope.categoryTree = data;
	});

  	$scope.selectType = function(item) {
  		console.log(item);
  	}

  	function clearData() {
  		$scope.courses = [];
  		$scope.start = 0;
  	}

  	$scope.selectSort = function(sort) {
  		$scope.sort = sort;
  		clearData();
  		$scope.loadCourseList(sort);
  	}

  	$scope.onRefresh = function() {
  		clearData();
  		$scope.loadCourseList($scope.sort);
  	}

  	$scope.categorySelectedListener = function() {
  		clearData();
  		$scope.loadCourseList($scope.sort);
  	}

}

function FoundCourseController($scope, SchoolService)
{
	console.log("FoundCourseController");
	SchoolService.getSchoolBanner(function(data) {
		$scope.banners = data;
	});

	SchoolService.getRecommendCourses({ limit : 3 }, function(data) {
		$scope.recommedCourses = data.data;
	});
}

function FoundLiveController($scope, SchoolService, CourseService)
{
	console.log("FoundLiveController");

	SchoolService.getSchoolBanner(function(data) {
		$scope.banners = data;
	});

	CourseService.getAllLiveCourses({ limit : 3 }, function(data) {
		$scope.liveCourses = data.data;
	});
}

function FoundClassRoomController($scope, ClassRoomService, SchoolService)
{
	console.log("FoundClassRoomController");

	SchoolService.getSchoolBanner(function(data) {
		$scope.banners = data;
	});

  	ClassRoomService.getClassRooms({ limit : 3 }, function(data) {
  		$scope.classRooms = data.data;
  	});
}


function FoundTabController($scope, CategoryService, AppUtil, $state)
{
	console.log("FoundTabController");

	$scope.categorySelectedListener  = function(category) {
		$scope.closeModal();
		$state.go('courseList');
	};

	CategoryService.getCategorieTree(function(data) {
		$scope.categoryTree = data;
    		$scope.initCategory();
	});

	$scope.initCategory = function() {
		AppUtil.createModal(
			$scope,
			"view/category.html"
		);
	};
}

function MessageTabController($scope, $ionicModal, $ionicSideMenuDelegate)
{
	console.log("FoundTabCtrl");
}

function ContactTabController($scope)
{
	console.log("FoundTabCtrl");
}

function MainTabController($scope, $ionicModal, sideDelegate, $state)
{
	console.log("MainTabController");
	$scope.toggleView = function(view) {
		$state.go("slideView.mainTab." + view);
	};

	$scope.toggle = function() {
		console.log(sideDelegate);
	    sideDelegate.toggleMenu();
	};
}