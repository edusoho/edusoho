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
directive('uiTab', function() {
  return {
    restrict: 'A',
    link : function(scope, element, attrs) {

          var self = this;
          var scroller = element[0].querySelector('.ui-tab-content');
          var nav = element[0].querySelector('.ui-tab-nav');

          if ("empty"  != attrs.select) {
            angular.element(scroller.children[0]).addClass('current');
            angular.element(nav.children[0]).addClass('current');
          }

          this.currentPage = 0;
          scroller.style.width = "100%";

          this.itemWidth = scroller.children[0].clientWidth;
          this.scrollWidth = this.itemWidth * this.count;

          function changeTabContentHeight(height) {
              var tabContent = element[0].querySelector('.ui-tab-content');
              $(tabContent).height(height);
          }

          angular.forEach(nav.children, function(item) {
            angular.element(item).on("touchstart", function(e) {

                var tagetHasCurrent = $(item).hasClass('current');
                var tempCurrentPage = self.currentPage;
                self.currentPage = $(item).index();

                $(nav).children().removeClass('current');
                $(scroller).children().removeClass('current');

                if (tempCurrentPage == self.currentPage && "empty"  == attrs.select && tagetHasCurrent) {
                  changeTabContentHeight(0);
                  return;
                }

                var currentScrooler = angular.element(scroller.children[self.currentPage]);
                $(item).addClass('current');
                currentScrooler.addClass("current");
                changeTabContentHeight("100%");
            });
          });

          if ("empty"  == attrs.select) {
              scope.$on("closeTab", function(event, data) {
                angular.element(scroller.children[self.currentPage]).removeClass('current');
                angular.element(nav.children[self.currentPage]).removeClass('current');
                changeTabContentHeight(0);
              });
          }
    }
  }
}).
directive('imgError', function() {
  return {
    restrict: 'A',
    compile: function(tElem, tAttrs) {
            return { 
                post: function postLink(scope, element, attributes) {
                  var errorSrc = "";
                  switch (attributes.imgError) {
                    case "avatar":
                      errorSrc = app.viewFloder  + "img/avatar.jpg";
                      break;
                    case "course":
                      errorSrc = app.viewFloder  + "img/course_default.jpg";
                      break;
                    case "vip":
                      errorSrc = app.viewFloder  + "img/vip_default.jpg";
                      break;
                  }

                  element.on("error", function(e) {
                    element.attr("src", errorSrc);
                  });
                }
            };
    }
  }
}).
directive('back', function($window, $state) {
  return {
    restrict: 'A',
    compile: function(tElem, tAttrs) {
            return { 
                post: function postLink(scope, element, attributes) {

                  element.on("click", function(){
                    if (attributes["back"] == "go") {
                      $window.history.back();
                      return;
                    }
                    if (attributes["back"] == "close" && scope.close) {
                      scope.close();
                      return;
                    }
                    $state.go("slideView.mainTab.found");
                  });
                }
            };
    }
  }
}).
directive('ngHtml', function($window, $state) {
  return {
    restrict: 'A',
    compile: function(tElem, tAttrs) {
            return { 
                post: function postLink(scope, element, attributes) {
                  scope.$watch(attributes.ngHtml, function(newValue) {
                    element.html(newValue);
                  });
                }
            };
    }
  }
}).
directive('uiBar', function($window) {
  return {
    restrict: 'A',
    link : function(scope, element, attrs) {
        var toolEL = element[0].querySelector(".bar-tool");
        var titleEL = element[0].querySelector(".title");
        
        var toolELWidth = toolEL ? toolEL.offsetWidth : 44;
        titleEL.style.paddingRight = toolELWidth + "px";
        titleEL.style.paddingLeft = toolELWidth + "px";
    }
  }
}).
directive('ngClick', function($parse) {
  return {
    restrict: 'A',
    compile: function(tElem, tAttrs) {
            return function(scope, element, clickExpr) {
                  var clickHandler = angular.isFunction(clickExpr) ?
                    clickExpr :
                    $parse(clickExpr.ngClick);

                  $(element[0]).on("tap",function(){
                    scope.$apply(function() {
                      clickHandler(scope, {$event: (event)});
                    });
                  });

                  element.onclick = function(event) { };
          };
    }
  }
}).
directive('uiScroll', function($parse) {
  return {
    restrict: 'A',
    link : function(scope, element, attrs) {
      scope.$watch(attrs.data, function(newValue) {

          if (newValue) {
                if (angular.isArray(newValue) && newValue.length == 0) {
                  return;
                }
                var uiHead = element[0].querySelector(".ui-details-head");
                element.on("scroll", function() {
                  var scrollHeight = element[0].scrollHeight;
                  var scrollTop = element[0].scrollTop;
                  var clientHeight = element[0].clientHeight;

                  if (attrs.onScroll) {
                    scope.headTop = uiHead.offsetHeight;
                    scope.scrollTop = scrollTop;
                    $parse(attrs.onScroll)(scope);
                  }
                  if ( !scope.isLoading && ( (scrollTop + clientHeight) >= scrollHeight ) ) {
                    scope.isLoading = true;
                    $parse(attrs.onInfinite)(scope);
                  }
                });
          }
      });
      
    }
  }
}).
directive('uiSliderBox', function() {
  return {
    restrict: 'A',
    link : function(scope, element, attrs) {
          scope.$watch(attrs.uiSliderBox, function(newValue) {
            if (newValue && newValue.length > 0) {
                initSlider();
            }
          });

          function initSlider () {
              var slider = new fz.Scroll('.' + attrs.slider, {
                  role: 'slider',
                  indicator: true,
                  autoplay: false,
                  interval: 3000
              });

              slider.on('beforeScrollStart', function(fromIndex, toIndex) {

              });

              slider.on('scrollEnd', function() {

              });
          }
          
    }
  }
}).
directive('slideIndex', function() {
  return {
    restrict: 'A',
    link : function(scope, element, attrs) {
          var total = 0;
          var $currentIndex = 0;
          scope.slideHasChanged = function($index) {
            $currentIndex = $index;
            changeSlidePointStatus();
          }

          scope.$watch("banners", function(newValue) {
            if (newValue && newValue.length > 0) {
                total = newValue.length;
                initSlideIndex();
            }
          });
          
          function changeSlidePointStatus()
          {
            angular.forEach(element[0].querySelectorAll('.point'), function(item, index){

              if (index == $currentIndex) {
                item.classList.add("active");
              } else {
                item.classList.remove("active");
              }
            });
          }

          function initSlideIndex() {
                var points = "";
            
                for (var i = 0 ; i < total; i++) {
                  if (i == $currentIndex) {
                    points += "<span class='point active'></span>";
                  } else {
                    points += "<span class='point'></span>";
                  }
                  
                };

                element.append("<p class='slide-index'>" + points + "</p>");
          }
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
directive('modal', function () {
  return {
    restrict: 'EA',
    priority : 10000,
    controller : function($scope, $element) {
    },
    link : function(scope, element, attrs) {
      element.addClass("ui-modal");
      element.on('click', function(event) {
        scope.$emit("closeTab", {});
      });

    }
  }
}).
directive('listEmptyView', function () {
  return {
    restrict: 'EA',
    link : function(scope, element, attrs) {
      var html = '<div class="list-empty">' + 
      '<a> <i class="icon iconfont icon-ebook"></i> <span>' + attrs.title + '</span> </a>' +
      '</div>';
      scope.$watch(attrs.data, function(newValue) {
        if (newValue && newValue.length == 0) {
          element.html(html);
        } else {
          element.html("");
        }
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

        link : function(scope, element, attrs) {

          function initCategory($scope) {
            var realityDepth = $scope.data.realityDepth > 3 ? 3 : $scope.data.realityDepth - 1;
            var categoryCols = [];
            var categoryTree = $scope.data.data[0];
            for (var i = realityDepth- 1; i >= 0; i--) {
                categoryCols[i] = [];
            };

            var getRootCategory = function(categoryId) {
              return {
                name : "全部",
                id : categoryId ? categoryId : 0
              }
            };

            categoryCols[0] = [getRootCategory()].concat(categoryTree.childs);
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
                      $scope.categoryCols[categoryColIndex] = [getRootCategory(category.id)].concat(category.childs);
                      $event.stopPropagation();
                    } else {
                      $scope.listener(category);
                    }
            };
          }

          scope.$watch("data", function(newValue) {
            if (newValue) {
                initCategory(scope);
            }
          });
        }
    };
});