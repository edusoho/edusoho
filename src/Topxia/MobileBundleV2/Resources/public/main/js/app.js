var app = angular.module('app', [
            'ngSanitize',
            'ui.router',
            'AppService',
            'AppFactory',
            'AppProvider',
            'ngSideView',
            'pasvaz.bindonce'
  ]);

app.version = "1.1.0";
app.viewFloder = "/bundles/topxiamobilebundlev2/main/";

app.config(['$httpProvider', function($httpProvider) {

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
}]);

app.config([ 'appRouterProvider', '$urlRouterProvider', function(appRouterProvider, $urlRouterProvider)
{
  $urlRouterProvider.when("/", "/index").
  otherwise('/index');

  appRouterProvider.init();
}]);

app.run(["applicationProvider", "$rootScope", '$timeout', 'platformUtil',
  function(applicationProvider, $rootScope, $timeout, platformUtil) {

  $rootScope.platform = platformUtil;
  $rootScope.showLoad = function(template) {
    if ($rootScope.loadPop) {
      $rootScope.loadPop.loading("hide");
    }
    $rootScope.loadPop = $.loading({
        content: "加载中...",
    });

    $timeout(function(){
        if ($rootScope.loadPop) {
          $rootScope.loadPop.loading("hide");
        }
    },5000);
  };

  $rootScope.toast = function(template) {
    var el = $.tips({
        content: template,
        stayTime: 2000,
        type: "info"
    });

    $timeout(function(){
        el.loading("hide");
    },2000);
  };

  $rootScope.hideLoad = function() {
    if (! $rootScope.loadPop) {
      return;
    }
    $rootScope.loadPop.loading("hide");
    $rootScope.loadPop = null;
  };

  app.host = window.location.origin;
  app.rootPath = window.location.origin + window.location.pathname;
  $rootScope.stateParams = {};

  console.log("app run");
  applicationProvider.init(app.host);
  FastClick.attach(document.body);
}]);

angular.element(document).ready(function() {
    var platformUtil = angular.injector(["AppFactory", "ng"]).get("platformUtil");
    if (platformUtil.native) {
      if (platformUtil.android && window.cordova.isDeviceready()) {
        angular.bootstrap( document, ["app"] );
        return;
      }
      document.addEventListener("deviceready", function() {
          angular.bootstrap( document, ["app"] );
      });
      return;
    }
    
    angular.bootstrap( document, ["app"] );
});
