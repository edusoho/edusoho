function MainTabController($scope, sideDelegate, $state) {
    console.log("MainTabController");
}

app.controller('FoundTabController', ['$scope', 'CategoryService', 'AppUtil', 'sideDelegate', '$state', FoundTabController]);

function FoundTabController($scope, CategoryService, AppUtil, cordovaUtil, $state) {
    console.log("FoundTabController");
    $scope.toggleView = function(view) {
        $state.go("slideView.mainTab." + view);
    };

    $scope.toggle = function() {
        
        if ($scope.platform.native) {
            return;
        }

        cordovaUtil.openDrawer("open");
    };

    $scope.categorySelectedListener = function(category) {
        cordovaUtil.openWebView(app.rootPath + "#/courselist/" + category.id);
        $(".ui-dialog").dialog("hide");
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
            cordovaUtil.openWebView(app.rootPath + "#/course/" + params);
        }

        this.webviewAction = function(params) {
            cordovaUtil.openWebView(params);
        }

        this.noneAction = function() {}

        return this[action + "Action"];
    }

    $scope.bannerClick = function(banner) {
        var bannerAction = self.parseBannerAction(banner.action);
        bannerAction(banner.params);
    }

    $scope.loadPage = function(pageName) {
        $scope[pageName] = 'view/found_' + pageName + '.html';
        $scope.$apply();
    }
}


app.controller('MessageTabController', ['$scope', FoundTabController]);

function MessageTabController($scope) {
    console.log("MessageTabController");
}

app.controller('ContactTabController', ['$scope', ContactTabController]);

function ContactTabController($scope) {
    console.log("ContactTabController");
}
