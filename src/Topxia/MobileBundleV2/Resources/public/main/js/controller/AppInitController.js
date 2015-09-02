app.controller('AppInitController', ['$scope', '$state', 'sideDelegate', 'SchoolService', AppInitController]);

function baseController($scope)
{
	this.getService = function(name) {
		return angular.injector(["AppService", "ng"]).get(name);
	}
}

function AppInitController($scope, $state, sideDelegate, SchoolService)
{	
	console.log("AppInitController");
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

	SchoolService.getSchoolPlugins(null, function(data) {
		$scope.plugins = data;
	});
}