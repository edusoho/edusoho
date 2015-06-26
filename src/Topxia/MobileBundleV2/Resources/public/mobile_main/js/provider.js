var appProvider= angular.module('AppProvider', []);
appProvider.provider('applicationProvider', function() {

	var self = this;
	this.$get = function(localStore, $rootScope, $q, cordovaUtil) {
		var application = {
			host : null,
			user : null,
			token : null
		};

		application.setHost = function(host){
			this.host = host;
		}

		application.init = function(host) {
			application.setHost(host);
			cordovaUtil.getUserToken($q, function(data) {
				application.user = data.user;
				application.token = data.token;
      				application.updateScope($rootScope);
			});
		}

		application.clearUser = function() {
			this.user = null;
			this.token = null;
			$rootScope.user = null;
			$rootScope.token = null;
			cordovaUtil.clearUserToken();
		}

		application.setUser = function(user, token) {
			this.user = user;
			this.token = token;
			this.updateScope($rootScope);
			cordovaUtil.saveUserToken(user, token);
		}

		application.updateScope = function($scope) {
			$scope.user = application.user;
			$scope.token = application.token;
		}
	    	return application;
	  }
});