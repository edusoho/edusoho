function MainTabController($scope, sideDelegate, $state)
{
	console.log("MainTabController");
}

app.controller('FoundTabController', ['$scope', 'CategoryService', 'AppUtil', 'sideDelegate', '$state', FoundTabController]);

function FoundTabController($scope, CategoryService, AppUtil, sideDelegate, $state)
{
	console.log("FoundTabController");
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

	$scope.categorySelectedListener  = function(category) {
		$state.go('courseList' , { categoryId : category.id } );
	};

	CategoryService.getCategorieTree(function(data) {
		$scope.categoryTree = data;
    		$scope.openModal = function($event) {
    			var dialog = $(".ui-dialog");
			dialog.dialog("show");
			$(".ui-dialog-bg").click(function(e) {
				dialog.dialog("hide");
			});
		};
	});

	var self = this;
	this.parseBannerAction = function(action) {
		this.courseAction = function(params) {
			if ($scope.platform.native) {
				esNativeCore.openWebView(app.rootPath + "#/course/" + params);
				return;
			}
			$state.go("course", { courseId : params });
		}

		this.webviewAction = function(params) {
			if ($scope.platform.native) {
				esNativeCore.openWebView(app.rootPath + params);
				return;
			}
			window.location.href = params;
		}

		this.noneAction = function() {
		}

		return this[action + "Action"];
	}

	$scope.bannerClick = function(banner) {
		var bannerAction = self.parseBannerAction(banner.action);
		bannerAction(banner.params);
	}
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
