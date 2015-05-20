app.directive('onContentLoaded', function ($parse) {
    return {
        restrict: 'A',
        compile: function(tElem, tAttrs) {

            function bindImgClick(imgs) {
              var imageArray = new Array();
              for (var i = 0; i < imgs.length; i++) {
                var img = imgs[i];
                img.alt = i;
                imageArray.push(img.src);
                img.addEventListener('click',
                function() {
                  esNativeCore.showImages(this.alt,imageArray);
                })
              }
            }

            return { 
                post: function postLink(scope, element, attributes) { 

                  var ngBindHtmlGetter = $parse(tAttrs.onContentLoaded);
                  var ngBindHtmlWatch = $parse(tAttrs.onContentLoaded, function getStringValue(value) {
                    return (value || '').toString();
                  });
                    scope.$watch(ngBindHtmlWatch, function() {
                        element.html(ngBindHtmlGetter(scope));
                        bindImgClick(element.find("img"));
                    });
                }  
            };
        }
    };
}).
directive('back', function($state) {
  return {
    restrict: 'A',
    compile: function(tElem, tAttrs) {
            return { 
                post: function postLink(scope, element, attributes) {
                  element.on("click", function(){
                    $state.go("slideView.mainTab.found");
                  });
                }
            };
    }
  }
}).
directive('slideInit', function() {
  return {
    restrict: 'A',
    link : function(scope, element) {
          element[0].querySelector('.banner').style.width = element.parent().parent()[0].offsetWidth + "px";
    }
  }
}).
directive('lazyLoad', function () {
  return function(scope, elm, attr) {
            echo.init({
            root:elm[0],
            offset: 100,
            throttle: 250,
            unload: false,
            callback: function (element, op) {

            }
        });
    }
}).
directive('modal', function ($ionicTabsDelegate) {
  return {
    restrict: 'EA',
    priority : 10000,
    controller : function($scope, $element) {
    },
    link : function(scope, element, attrs) {
      attrs.nsShow = "$parent.$tabSelected";
      element[0].addEventListener('click', function(event) {
        $ionicTabsDelegate.select(0);
        scope.$apply(function() {
          scope.$parent.$tabSelected = false;
        });
      });
    }
  }
}).
directive('categoryTree', function () {
    return {
        restrict: 'E',
        scope: {  
            data: '=data',
            listener : '=listener'
        }, 
        templateUrl: app.viewFloder + 'view/category_tree.html', 
        controller: function($scope, $element){

            var realityDepth = $scope.data.realityDepth > 3 ? 3 : $scope.data.realityDepth - 1;
            var categoryCols = [];
            var categoryTree = $scope.data.data[0];
            for (var i = realityDepth- 1; i >= 0; i--) {
                categoryCols[i] = [];
            };

            categoryCols[0] = categoryTree.childs;
            $scope.categoryCols = categoryCols;

            $scope.selectCategory = function($event, category) {
                    if (category.childs) {
                      for (var i = $scope.categoryCols.length- 1; i >= category.depth; i--) {
                          $scope.categoryCols[i] = [];
                      };
                      var categoryColIndex = category.depth;
                      if (category.depth > 2) {
                        categoryColIndex = 2;
                      }
                      $scope.categoryCols[categoryColIndex] = category.childs;
                      $event.stopPropagation();
                    } else {
                      $scope.listener(category);
                    }
            };
        },
        compile: function(tElem, tAttrs) {

            return { 
                post: function postLink(scope, element, attributes) { 
                }  
            };
        }
    };
});