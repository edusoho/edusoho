app.controller('CourseController', ['$scope', '$stateParams', 'ServcieUtil', 'AppUtil', '$state', CourseController]);
app.controller('CourseDetailController', ['$scope', '$stateParams', 'CourseService', CourseDetailController]);
app.controller('CourseSettingController', ['$scope', '$stateParams', 'CourseService', '$window', CourseSettingController]);

function CourseSettingController($scope, $stateParams, CourseService, $window)
{
  $scope.isLearn = $stateParams.isLearn;
  $scope.exitLearnCourse = function() {
    $scope.showLoad();
    CourseService.unLearnCourse({
      courseId : $stateParams.courseId,
      token : $scope.token
    }, function(data) {
      $scope.hideLoad();
      if (data.meta.code == 200) {
        $window.history.back();
        setTimeout(function() {
          $scope.$emit("refresh", {});
        }, 10);
        
      } else {
        $scope.toast(data.meta.message);
      }
    });
  }
}

function CourseDetailController($scope, $stateParams, CourseService)
{
  CourseService.getCourse({
      courseId : $stateParams.courseId,
      token : $scope.token
    }, function(data) {
      $scope.course = data.course;
    });
}

function CourseController($scope, $stateParams, ServcieUtil, AppUtil, $state)
{
    $scope.showLoad();

    var CourseService = ServcieUtil.getService("CourseService");
    var LessonService = ServcieUtil.getService("LessonService");

    CourseService.getCourse({
      courseId : $stateParams.courseId,
      token : $scope.token
    }, function(data) {
      $scope.ratingArray = AppUtil.createArray(5);
      $scope.vipLevels = data.vipLevels;
      $scope.course = data.course;
      $scope.member = data.member;
      $scope.isFavorited = data.userFavorited;
      $scope.discount = data.discount;

      if (data.member) {
        var progress = data.course.lessonNum == 0 ? 0 : data.member.learnedNum / data.course.lessonNum;
        $scope.learnProgress = (progress * 100) + "%" ;
      }
      $scope.courseView = app.viewFloder + (data.member ? "view/course_learn.html" : "view/course_no_learn.html");
      $scope.hideLoad();
      $scope.loadLessons();
      $scope.loadReviews();
    });

    $scope.loadLessons = function() {
      LessonService.getCourseLessons({
        courseId : $stateParams.courseId,
        token : $scope.token
      }, function(data) {
        $scope.$apply(function() {
          $scope.lessons = data.lessons;
          $scope.learnStatuses = data.learnStatuses;

          for( index in data.learnStatuses ) {
            $scope.lastLearnStatusIndex = index;
          }
          
        });
      });
    }

    $scope.loadReviews = function(){
      CourseService.getReviews({
        courseId : $stateParams.courseId,
        token : $scope.token,
        limit : 1
      }, function(data) {
        $scope.reviews = data.data;
      });
    }

    $scope.favoriteCourse = function() {
      if ($scope.user == null) {
        $state.go("login", { goto : "/course/" + $stateParams.courseId });
        return;
      }
      var params = {
          courseId : $stateParams.courseId,
          token : $scope.token
      };

      if ($scope.isFavorited) {
        CourseService.unFavoriteCourse(params, function(data) {
          if (data == true) {
            $scope.$apply(function() {
              $scope.isFavorited = false;
            });
          }
        });
      } else {
        CourseService.favoriteCourse(params, function(data) {
          if (data == true) {
            $scope.$apply(function() {
              $scope.isFavorited = true;
            });
          }
        });
      }
    }

    var self = this;
    this.payCourse = function() {
      ServcieUtil.getService("OrderService").payCourse({
        courseId : $stateParams.courseId,
        token : $scope.token
      }, function(data) {
        if (data.paid == true) {
          window.location.reload();
        } else {
          $scope.toast("加入学习失败!");
        }
      }, function(error) {
        console.log(error);
      });
    }

    $scope.vipLeand = function() {
      if ($scope.user == null) {
        $state.go("login", { goto : "/course/" + $stateParams.courseId });
        return;
      }
      CourseService.vipLearn({
        courseId : $stateParams.courseId,
        token : $scope.token
      }, function(data){
        if (data.meta.code == 200) {
          window.location.reload();
        } else {
          $scope.toast(data.meta.message);
        }
      }, function(error) {
        console.log(error);
      });
    }

    $scope.joinCourse = function() {
      if ($scope.user == null) {
        $state.go("login", { goto : "/course/" + $stateParams.courseId });
        return;
      }
      if ($scope.course.price <= 0) {
        self.payCourse();
      } else {
        $state.go("coursePay", { courseId : $scope.course.id });
      }
      
    }

    $scope.$parent.$on("refresh", function(event, data) {
      window.location.reload();
    });

    $scope.onTabSelected = function(index) {
      $ionicScrollDelegate.$getByHandle("mainScroll").resize();
      
      if ($scope.showTopTab) {
        var topSelectedIndex = $ionicTabsDelegate.$getByHandle("topTabHandle").selectedIndex();
        $scope.selectedIndex = topSelectedIndex;
        $ionicTabsDelegate.$getByHandle("tabHandle").select(topSelectedIndex);
      } else {
        var selectedIndex = $ionicTabsDelegate.$getByHandle("tabHandle").selectedIndex();
        $scope.selectedIndex = selectedIndex;
        $ionicTabsDelegate.$getByHandle("topTabHandle").select(selectedIndex);
      }
      
    }

    var content, headBody;

    function topTabChange(scrollTop) {
      if (headBody == null) {
        content  =$ionicScrollDelegate.$getByHandle("mainScroll").getScrollView().__content;
        headBody = content.querySelector('.course-head-body');
      }

      $scope.$apply(function() {
        $scope.showTopTab = scrollTop > headBody.offsetHeight;
      });
    }

    $scope.contentMove = function(scrollTop, scrollLeft) {
      topTabChange(scrollTop);
    }

    $scope.scrollComplete = function() {
      var scrollTop = $ionicScrollDelegate.$getByHandle("mainScroll").getScrollView().__scrollTop
      topTabChange(scrollTop);
    }
}