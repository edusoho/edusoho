define(function(require, exports, module) {
	require("AppControllers");
	require("AppServices");
	require("AppFilters");
	require("frozen");

	var app = angular.module('EduSohoArticleApp', [
		'ionic',
		'AppControllers',
		'AppServices',
		'AppFilters'
		]);

	var AppDirectives = require("AppDirectives");
	AppDirectives.init(app);

	app.config(function($httpProvider) {
	    $httpProvider.defaults.headers.put['Content-Type'] = 'application/x-www-form-urlencoded';
	    $httpProvider.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded';

	    // Override $http service's default transformRequest
	    $httpProvider.defaults.transformRequest = [function(data) {
	        /**
	         * The workhorse; converts an object to x-www-form-urlencoded serialization.
	         * @param {Object} obj
	         * @return {String}
	         */
	        var param = function(obj) {
	            var query = '';
	            var name, value, fullSubName, subName, subValue, innerObj, i;
	 
	            for (name in obj) {
	                value = obj[name];
	 
	                if (value instanceof Array) {
	                    for (i = 0; i < value.length; ++i) {
	                        subValue = value[i];
	                        fullSubName = name + '[' + i + ']';
	                        innerObj = {};
	                        innerObj[fullSubName] = subValue;
	                        query += param(innerObj) + '&';
	                    }
	                } else if (value instanceof Object) {
	                    for (subName in value) {
	                        subValue = value[subName];
	                        fullSubName = name + '[' + subName + ']';
	                        innerObj = {};
	                        innerObj[fullSubName] = subValue;
	                        query += param(innerObj) + '&';
	                    }
	                } else if (value !== undefined && value !== null) {
	                    query += encodeURIComponent(name) + '='
	                            + encodeURIComponent(value) + '&';
	                }
	            }
	 
	            return query.length ? query.substr(0, query.length - 1) : query;
	        };
	 
	        return angular.isObject(data) && String(data) !== '[object File]'
	                ? param(data)
	                : data;
	    }];
	});
	
	app.config([ '$stateProvider', '$urlRouterProvider', function($stateProvider, $urlRouterProvider)
	{
		$urlRouterProvider.when("/", "/index").
		otherwise('/');

		$stateProvider.
		state("article",{
			url : "/index",
			views : {
				"categoryList" : {
					templateUrl : '/bundles/topxiamobilebundlev2/article/view/category.html',
					controller : CategoryController
				},
				"articleContent" : {
					templateUrl : '/bundles/topxiamobilebundlev2/article/view/list.html',
					controller : ListController
				}
			}
		}).
		state("detail", {
			url : "/detail/:id",
			views : {
				"articleContent" :{
					template : "<ion-content><div on-content-loaded='content'></div></ion-content>",
					controller : DetailController
				}
			}
		});
	}]);

	angular.element(document).ready(function() {
		         angular.bootstrap(document, ['EduSohoArticleApp']);
		         angular.$client = {};
	});
});