app.controller('MainController', ['$scope', '$ionicModal', 'sideDelegate', '$state', MainTabController]);

function MainTabController($scope, $ionicModal, sideDelegate, $state)
{
	console.log("MainTabController");
	$scope.toggleView = function(view) {
		$state.go("slideView.mainTab." + view);
	};

	$scope.toggle = function() {
		if ($scope.platform.native) {
			window.esNativeCore.openDrawer("open");
			return;
		}

		sideDelegate.toggleMenu();
	};
}

app.controller('FoundTabController', ['$scope', 'CategoryService', 'AppUtil', '$state', FoundTabController]);

function FoundTabController($scope, CategoryService, AppUtil, $state)
{
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
			app.viewFloder + "view/category.html"
		);
	};
}


app.controller('MessageTabController', ['$scope', FoundTabController]);

function MessageTabController($scope)
{
	console.log("MessageTabController");
}

app.controller('ContactTabController', ['$scope', ContactTabController]);

function ContactTabController($scope)
{
	console.log("ContactTabController");
	console.log($scope);
}
