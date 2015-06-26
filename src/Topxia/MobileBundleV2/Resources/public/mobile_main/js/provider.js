var appProvider= angular.module('AppProvider', []);
appProvider.provider('applicationProvider', function() {

	var self = this;
	this.$get = function(localStore, $rootScope, $q) {
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
			if ($rootScope.platform.native) {
				var promise = esNativeCore.getUserToken($q);
				promise.then(function(data) {
					application.user = data.user;
					application.token = data.token;
      					application.updateScope($rootScope);
				});
				return;
			}
			application.user = angular.fromJson(localStore.get("user"));
			application.token = localStore.get("token");
			application.updateScope($rootScope);
		}

		application.clearUser = function() {
			this.user = null;
			this.token = null;
			$rootScope.user = null;
			$rootScope.token = null;
			localStore.remove("user");
			localStore.remove("token");
			if ($rootScope.platform.native) {
				esNativeCore.clearUserToken();
			}
		}

		application.setUser = function(user, token) {
			this.user = user;
			this.token = token;
			this.updateScope($rootScope);
			localStore.save("user", angular.toJson(user));
			localStore.save("token", token);
			if ($rootScope.platform.native) {
				console.log(token);
				esNativeCore.saveUserToken(user, token);
			}
		}

		application.updateScope = function($scope) {
			$scope.user = application.user;
			$scope.token = application.token;
		}
	    	return application;
	  }
});