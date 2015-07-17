app.controller('CourseController', ['$scope', '$stateParams', 'CourseService', 'AppUtil', '$state', 'cordovaUtil', CourseController]);
app.controller('CourseDetailController', ['$scope', '$stateParams', 'CourseService', CourseDetailController]);
app.controller('CourseSettingController', ['$scope', '$stateParams', 'CourseService', '$window', CourseSettingController]);

function CourseReviewController($scope, $stateParams, CourseService, $window)
{

  var self = this;
  $scope.canLoad = true;
  $scope.start = $scope.start || 0;

  $scope.loadMore = function(){
        if (! $scope.canLoad) {
          return;
        }
       setTimeout(function() {
          self.loadReviews();
       }, 200);
  };

  this.loadReviews = function() {
    CourseService.getReviews({
      start : $scope.start,
      limit : 10,
      courseId : $stateParams.courseId
    }, function(data) {

      var length  = data ? data.data.length : 0;
      if (!data || length == 0 || length < 10) {
          $scope.canLoad = false;
      }

      $scope.reviews = $scope.reviews || [];
      for (var i = 0; i < length; i++) {
        $scope.reviews.push(data.data[i]);
      };

      $scope.start += data.limit;

    });
  }

  this.loadReviewInfo = function() {
    CourseService.getCourseReviewInfo({
      courseId : $stateParams.courseId
    }, function(data) {
      $scope.reviewData = data;
    });
  }
  
  this.loadReviewInfo();
  this.loadReviews();
}

function CourseSettingController($scope, $stateParams, CourseService, $window)
{
  $scope.isLearn = $stateParams.isLearn;
  $scope.exitLearnCourse = function() {
    $scope.showLoad();
    CourseService.unLearnCourse({
      courseId : $stateParams.courseId
    }, function(data) {
      $scope.hideLoad();
      if (! data.error) {
        $window.history.back();
        setTimeout(function() {
          $scope.$emit("refresh", {});
        }, 10);
        
      } else {
        $scope.toast(data.error.message);
      }
    });
  }
}

function CourseDetailController($scope, $stateParams, CourseService)
{
  CourseService.getCourse({
      courseId : $stateParams.courseId
    }, function(data) {
      $scope.course = data.course;
    });
}

app.controller('CourseToolController', ['$scope', '$stateParams', 'OrderService', 'CourseService', 'cordovaUtil', '$state', CourseToolController]);
function CourseToolController($scope, $stateParams, OrderService, CourseService, cordovaUtil, $state)
{
    var self = this;
    this.payCourse = function() {
      OrderService.payCourse({
        courseId : $stateParams.courseId
      }, function(data) {
        if (data.paid == true) {
          console.log("reload");
          window.location.reload();
        } else {
          var error = "加入学习失败";
          if (data.error) {
            error = data.error.message;
          } 
          $scope.toast(error);
        }
      }, function(error) {
        console.log(error);
      });
    }

    $scope.vipLeand = function() {
      if ($scope.user == null) {
        cordovaUtil.openWebView(app.rootPath + "#/login/course");
        return;
      }
      CourseService.vipLearn({
        courseId : $stateParams.courseId
      }, function(data){
        if (! data.error) {
          window.location.reload();
        } else {
          $scope.toast(data.error.message);
        }
      }, function(error) {
        console.log(error);
      });
    }

    $scope.joinCourse = function() {
      if ($scope.user == null) {
        cordovaUtil.openWebView(app.rootPath + "#/login/course");
        return;
      }
      if ($scope.course.price <= 0) {
        self.payCourse();
      } else {
        $state.go("coursePay", { courseId : $scope.course.id });
      }
      
    }

    $scope.favoriteCourse = function() {
      if ($scope.user == null) {
        cordovaUtil.openWebView(app.rootPath + "#/login/course");
        return;
      }
      var params = {
          courseId : $stateParams.courseId
      };

      if ($scope.isFavorited) {
        CourseService.unFavoriteCourse(params, function(data) {
          if (data == true) {
            $scope.isFavorited = false;
          }
        });
      } else {
        CourseService.favoriteCourse(params, function(data) {
          if (data == true) {
            $scope.isFavorited = true;
          }
        });
      }
    }

    $scope.shardCourse = function() {
      var about = $scope.course.about;
      if (about.length > 20) {
        about = about.substring(0, 20);
      }
      cordovaUtil.share(app.host + "/course/" + $scope.course.id, $scope.course.title, about, $scope.course.largePicture);
    }
}

function CourseController($scope, $stateParams, CourseService, AppUtil, $state, cordovaUtil)
{
    $scope.showLoad();

    CourseService.getCourse({
      courseId : $stateParams.courseId
    }, function(data) {
      if (data && data.error) {
        var dia = $.dialog({
                title : '课程预览' ,
                content : data.error.message,
                button : [ "确认" ]
        });

        dia.on("dialog:action",function(e){
                cordovaUtil.closeWebView();
        });
        return;
      }
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
    });

    $scope.loadReviews = function(){
      CourseService.getReviews({
        courseId : $stateParams.courseId,
        limit : 1
      }, function(data) {
        $scope.reviews = data.data;
      });
    }

    $scope.exitLearnCourse = function() {
      $scope.showLoad();
      CourseService.unLearnCourse({
        courseId : $stateParams.courseId,
        token : $scope.token
      }, function(data) {
        $scope.hideLoad();
        if (! data.error) {
          window.location.reload();
        } else {
          $scope.toast(data.error.message);
        }
      });
    }

    $scope.showDownLesson = function() {
      cordovaUtil.showDownLesson($scope.course.id);
    }

    $scope.$parent.$on("refresh", function(event, data) {
      window.location.reload();
    });
}