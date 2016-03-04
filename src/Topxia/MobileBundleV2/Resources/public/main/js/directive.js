app.directive('onElementReadey', function ($parse, $timeout) {
    return {
        restrict: 'A',
        link : function(scope, element, attrs) {
          $timeout(function() {
            $parse(attrs.onElementReadey)(scope);
           }, 100);
        }
    };
}).
directive('uiServicePanel', function($timeout) {
  return {
        restrict: 'A',
        link : function(scope, element, attrs) {
          var list = element[0].querySelector(".ui-list");
          var btn = element[0].querySelector(".service-btn");
          var expandIcon = angular.element(element[0].querySelector(".service-icon"));

          var btnElement = angular.element(btn);
          btnElement.on('click', function(e) {
            
            var expand = btnElement.attr("expand");
            btnElement.attr("expand", "true" == expand ? "false" : "true");
            expandIcon.css("-webkit-transform", ("true" == expand ? "rotate(-180deg)" : "rotate(0)"));

            var length = list.children.length;
            for (var i = 2; i < length; i++) {
              list.children[i].style.display = ("true" == expand ? 'none' : 'block');
            };
          });
          //$(titleLabel).animate({ 'left' : left + 'px' }, 500, 'ease-out');
        }
    };
}).
directive('uiAutoPanel', function () {
  return {
        restrict: 'A',
        link : function(scope, element, attrs) {
          element.attr("close", "true");
          var autoBtn = element[0].querySelector(".ui-panel-autobtn");
          var content = element[0].querySelector(".ui-panel-content");

          scope.$watch(attrs.data, function(newValue) {
            if (newValue) {
              initAutoBtn();
            }
          });

          function initAutoBtn() {

            if (200 > content.offsetHeight) {
              autoBtn.style.display = 'none';
              return;
            }
            content.style.height = '200px';
            var expand = angular.element(autoBtn.querySelector(".autobtn-icon"));
            var autoBtnText = autoBtn.querySelector(".autobtn-text");
            
            angular.element(autoBtn).on('click', function() {
              var isClose = element.attr("close");
              if ("true" == isClose) {
                  autoBtnText.innerText = "合并";
                  content.style.overflow = 'auto';
                  content.style.height = 'auto';
                  expand.removeClass("icon-expandmore");
                  expand.addClass("icon-expandless");
                  element.attr("close", "false");
              } else {
                  autoBtnText.innerText = "展开";
                  content.style.overflow = 'hidden';
                  content.style.height = '200px';
                  expand.addClass("icon-expandmore");
                  expand.removeClass("icon-expandless");
                  element.attr("close", "true");
              }
            });
          }
        }
    };
}).
directive('uiTab', function ($parse) {
  return {
    restrict: 'A',
    link : function(scope, element, attrs) {

          var self = this;
          var scroller = element[0].querySelector('.ui-tab-content');
          var nav = element[0].querySelector('.ui-tab-nav');

          function itemOnLoadListener(currentItem) {
            var isFirstRun = currentItem.attr("isFirstRun");
            var itemOnLoad = currentItem.attr("ng-onload");
            if ("true" != isFirstRun) {
              if (itemOnLoad) {
                $parse(itemOnLoad)(scope);
              }
              
              currentItem.attr("isFirstRun", "true");
            }
          }

          if ("empty"  != attrs.select) {
            var childrenIndex = 0;
            var childrenElement;
            for (var i = 0; i < nav.children.length; i++) {
              if (angular.element(nav.children[i]).hasClass('current')) {
                childrenIndex = i;
                break;
              }
            };

            angular.element(scroller.children[childrenIndex]).addClass('current');
            angular.element(nav.children[childrenIndex]).addClass('current');
            itemOnLoadListener(angular.element(scroller.children[childrenIndex]));
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
            angular.element(item).on("click", function(e) {

                var currentItem = $(item);
                var tagetHasCurrent = currentItem.hasClass('current');
                var tempCurrentPage = self.currentPage;
                self.currentPage = currentItem.index();

                $(nav).children().removeClass('current');
                $(scroller).children().removeClass('current');

                if (tempCurrentPage == self.currentPage && "empty"  == attrs.select && tagetHasCurrent) {
                  changeTabContentHeight(0);
                  scope.$emit("tabClick", {
                    index : self.currentPage,
                    isShow : false
                  });
                  return;
                }

                var currentScrooler = angular.element(scroller.children[self.currentPage]);
                currentItem.addClass('current');
                currentScrooler.addClass("current");

                itemOnLoadListener(currentScrooler);
                changeTabContentHeight("100%");
                scope.$emit("tabClick", {
                    index : self.currentPage,
                    isShow : true
                });
            });
          });

          if ("empty"  == attrs.select) {
              scope.$on("closeTab", function(event, data) {
                angular.element(scroller.children[self.currentPage]).removeClass('current');
                angular.element(nav.children[self.currentPage]).removeClass('current');
                changeTabContentHeight(0);
                scope.$emit("tabClick", {
                    index : self.currentPage,
                    isShow : false
                });
              });
          }
    }
  }
}).
directive('imgError', function($timeout) {
  return {
    restrict: 'A',
    compile: function(tElem, tAttrs) {
            return {
                post: function postLink(scope, element, attributes) {
                  var errorSrc = "";
                  switch (attributes.imgError) {
                    case "avatar":
                      errorSrc = app.viewFloder  + "img/avatar.png";
                      break;
                    case "course":
                      errorSrc = app.viewFloder  + "img/default_course.jpg";
                      break;
                    case "vip":
                      errorSrc = app.viewFloder  + "img/vip_default.png";
                      break;
                    case "classroom":
                      errorSrc = app.viewFloder  + "img/default_class.jpg";
                      break;
                  }

                  element.attr('src', errorSrc);
                  element.on("error", function(e) {
                    element.attr("src", errorSrc);
                    element.on("error", null);
                  });

                  if ("classroom" == attributes.imgError
                     && attributes.imgSrc.indexOf("course-large.png") != -1) {
                    return;
                  }
                  $timeout(function() {
                    element.attr('src', attributes.imgSrc);
                  }, 100);
                }
            };
    }
  }
}).
directive('ngImgShow', function() {
  return {
    restrict: 'A',
    link : function(scope, element, attrs) {
      setTimeout(function() {
        var imageArray = [];
        angular.forEach(element[0].getElementsByTagName("img"), function(item, i) {
          imageArray.push(item.src);
          item.alt = i;
          item.addEventListener("click", function() {
            esNativeCore.showImages(this.alt, imageArray);
          });
        });
      }, 200);   
    }
  }
}).
directive('back', function(cordovaUtil, $state) {
  return {
    restrict: 'A',
    compile: function(tElem, tAttrs) {
            return { 
                post: function postLink(scope, element, attributes) {

                  element.on("click", function(){
                    if (attributes["back"] == "go") {
                      cordovaUtil.backWebView();
                      return;
                    }
                    if (attributes["back"] == "close" && scope.close) {
                      scope.close();
                      return;
                    }
                    $state.go("slideView.mainTab");
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
directive('uiPop', function($window) {
  return {
    restrict: 'A',
    compile: function(tElem, tAttrs) {
            return { 
                post: function postLink(scope, element, attributes) {

                    function changePopStatus() {
                      scope.$apply(function() {
                        scope.isShowMenuPop = ! scope.isShowMenuPop;
                      });
                    }

                    var popBtn = element[0].querySelector(".ui-pop-btn");
                    var popBg = element[0].querySelector(".ui-pop-bg");

                    popBg.style.width = $window.innerWidth + "px";
                    popBg.style.height = $window.innerHeight + "px";
                    angular.element(popBg).on("click", function(e) {
                      changePopStatus();
                    });

                    angular.element(popBtn).on("click", function(e) {
                      changePopStatus();
                    });
                }
           };
    }
  }
}).
directive('uiBar', function() {
  return {
    restrict: 'A',
    link : function(scope, element, attrs) {
        var toolEL = element[0].querySelector(".bar-tool");
        var titleEL = element[0].querySelector(".title");
        
        var toolELWidth = toolEL ? toolEL.offsetWidth : 44;
        toolELWidth = toolELWidth < 44 ? 44 : toolELWidth;
        titleEL.style.paddingRight = toolELWidth + "px";
        titleEL.style.paddingLeft = toolELWidth + "px";
    }
  }
}).
directive('ngHref', function($window) {
  return {
    restrict: 'A',
    compile: function(tElem, tAttrs) {
            return function ngEventHandler(scope, element, attr) {
              element.on("click", function(e) {
                var url = [$window.location.origin, $window.location.pathname, attr.ngHref].join("");
                if (scope.platform.native) {
                  esNativeCore.openWebView(url);
                  return;
                }
                $window.location.href = url;
              });
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
                    $parse(attrs.onInfinite)(scope, { successCallback : function() {
                      scope.isLoading = false;
                    } });
                  }
                });
          }
      });
      
    }
  }
}).
directive('uiSliderBox', function($parse) {
  return {
    restrict: 'A',
    link : function(scope, element, attrs) {
          scope.$watch(attrs.uiSliderBox, function(newValue) {
            if (newValue && newValue.length > 0) {
                initSlider();
                if (attrs.onLoad) {
                  $parse(attrs.onLoad)(scope, element);
                }
            }
          });

          if ("true" != attrs.auto && element[0].clientWidth) {
            element.css('height', (element[0].clientWidth / 1.9) + "px");
          }
          
          function initSlider () {
              var slider = new fz.Scroll('.' + attrs.slider, {
                  role: 'slider',
                  indicator: true,
                  autoplay: false,
                  interval: 3000
              });

              slider.on('beforeScrollStart', function(fromIndex, toIndex) {
                if (attrs.scrollChange) {
                  scope.scrollIndex = toIndex;
                  $parse(attrs.scrollChange)(scope);
                }
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
      element.addClass("item");
      element.on('click', function(event) {
        scope.$emit("closeTab", {});
        $(".ui-scroller").css("overflow","scroll");
      });

      scope.$on("tabClick", function(event, data) {
        if (!data.isShow) {
          $(".ui-scroller").css("overflow","scroll");
          return;
        }

        $(".ui-scroller").css("overflow","hidden");
      });

    }
  }
}).
directive('listEmptyView', function (AppUtil) {
  return {
    restrict: 'EA',
    link : function(scope, element, attrs) {
      var html = '<div class="list-empty"><a> <i class="icon iconfont icon-%1"></i> <span>%2</span> </a></div>';
      html = AppUtil.formatString(html, attrs.icon || "ebook", attrs.title);
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

            var changeItemBG = function(item) {
              var parentNode = item.parentNode;
              if (!parentNode) {
                return;
              }

              angular.forEach(parentNode.children, function(item) {
                angular.element(item).css("background", "none");
              });
            };

            $scope.selectCategory = function($event, category) {
                    
                    changeItemBG($event.srcElement.parentNode);
                    angular.element($event.srcElement.parentNode).css("background", "#ccc");
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