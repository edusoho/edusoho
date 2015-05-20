var appFactory = angular.module('AppFactory', []);
appFactory.factory('AppUtil', ['$rootScope', '$ionicModal', function($rootScope, $ionicModal) {
	var utils = {
		createArray : function(count) {
			var arr = [];
			for (var i = count- 1; i >= 0; i--) {
				arr.unshift(i);
			};

			return arr;
		},
		coverCategoty : function(categoryTree) {
			var categorys = [];
			for (var i = categoryTree.length - 1; i >= 0; i--) {
				categorys.unshift(categoryTree[i]);
			};
		},
		createModal : function($scope, templateUrl, modalInitFunc) {
			$ionicModal.fromTemplateUrl(templateUrl, {
			    	scope: $scope,
			    	animation: 'slide-in-down'
			}).then(function(modal) {
				if (modalInitFunc) {
					modalInitFunc(modal);
				}
				$scope.modal = modal;
			});

			$scope.openModal = function() {
				if ($scope.modal.isShown) {
					$scope.modal.show();
				} else {
					$scope.modal.hide();
				}
			};
			$scope.closeModal = function() {
				$scope.modal.hide();
			};

			$scope.$on('$destroy', function() {
				$scope.modal.remove();
			});
		}
	};
	
	return utils;
}]).
factory('broadCast', ['$rootScope', function($rootScope) {
	angular.broadQueue = [];
	var broadCastService = {
		bind : function(event, callback){
			angular.broadQueue[event] = callback;
		},
		send : function(event, msg){
			callback = angular.broadQueue[event];
			//delete angular.broadQueue[event];
			callback(event, msg);
		}
	};
	return broadCastService;
}]).
factory('CourseUtil', ['$rootScope', 'CourseService', 'ClassRoomService' ,function(
	$rootScope, CourseService, ClassRoomService) {
	
	return {
		getFavoriteListTypes : function() {
			return {
				'course' : {
					url : "Course/getFavoriteNormalCoruse",
					data : [],
					start : 0,
					canLoad : true
				},
				'live' : {
					url : "Course/getFavoriteLiveCoruse",
					data : [],
					start : 0,
					canLoad : true
				}
			}
		},

		getCourseListTypes  : function() {
			return [
		  		{
		  			name : "课程",
		  			type : "course"
		  		},
		  		{
		  			name : "班级",
		  			type : "classroom"
		  		},
		  		{
		  			name : "直播",
		  			type : "live"
		  		}
		  	]
		},

		getCourseListSorts : function() {
			return [
		  		{
		  			name : "最新",
		  			type : "latest"
		  		},
		  		{
		  			name : "最热",
		  			type : "popular"
		  		},
		  		{
		  			name : "推荐",
		  			type : "recommendedSeq"
		  		}
		  	]
		}
	};
}]).
factory('ImageUtil', ['$rootScope', function($rootScope){
	function getScreenWidth() {
		var width = window.screen.width;
		switch (window.orientation) {
		case 0:
			width = window.screen.width;
			break;
		case 90:
		case - 90 : width = window.screen.height;
			break
		}
		width = width * 0.96;
		return width
	}
	function zoomImage(img, width) {
		var oldH = img.height;
		var oldW = img.width;
		img.width = width;
		img.height = width / oldW * oldH
	}
	function adaptationImage() {
		var width = getScreenWidth();
		var imgs = angular.element(document.images);
		for (var i = 0; i < imgs.length; i++) {
			zoomImage(imgs[i], width)
		}
	}

	var util = {
		zoom : function(){
			var imageArray = new Array();
			var imgs = angular.element(document.images);
			for (var i = 0; i < imgs.length; i++) {
				var img = imgs[i];
				img.addEventListener('load',
				function() {
					var width = getScreenWidth();
					zoomImage(this, width)
				});
				img.alt = i;
				imageArray.push(img.src);
				img.addEventListener('click',
				function() {
					window.location = 'imageIndexNUrls://?' + this.alt + '.partation.' + imageArray.join('.partation.');
					window.jsobj.showImages(this.alt,imageArray);
				})
			}
			window.addEventListener('orientationchange',
			function() {
				adaptationImage()
			},
			false);
		}
	};

	return util;
	
}]);