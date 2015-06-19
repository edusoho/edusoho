angular.module('AppServices', []).
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