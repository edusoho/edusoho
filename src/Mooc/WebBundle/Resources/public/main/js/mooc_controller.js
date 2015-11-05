var CourseController = function($scope, $stateParams, CourseService, AppUtil, $state, cordovaUtil)
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
        $scope.learnProgress = ((progress * 100).toFixed(2)) + "%" ;
      }
      
      $scope.courseView = app.moocViewFloder + (data.member ? "view/course_learn.html" : "view/course_no_learn.html");
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

    $scope.exitLearnCourse = function(reason) {
      $scope.showLoad();
      CourseService.unLearnCourse({
        reason : reason,
        courseId : $stateParams.courseId
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

app.controller('MoocCourseToolController', ['$scope', '$stateParams', 'OrderService', 'CourseService', 'cordovaUtil', '$state', MoocCourseToolController]);
function MoocCourseToolController($scope, $stateParams, OrderService, CourseService, cordovaUtil, $state)
{
    this.__proto__ = new BaseToolController($scope, OrderService, cordovaUtil);
    var self = this;

    this.goToPay = function() {
      var course = $scope.course;
      var priceType = course.priceType;
      var price = "Coin" == priceType ? course.coinPrice : course.price;
      if (price <= 0) {
        self.payCourse(price, "course", $stateParams.courseId);
      } else {
        $state.go("coursePay", { targetId : $scope.course.id, targetType : 'course' });
      }
    };

    this.checkModifyUserInfo = function(modifyInfos) {
      for (var i = 0; i < modifyInfos.length; i++) {
        var modifyInfo = modifyInfos[i];
        if (!modifyInfo["content"] || modifyInfo["content"] == 0) {
          alert("请填写" + modifyInfo["title"]);
          return false;
        }
      };

      return true;
    }

    $scope.$parent.updateModifyInfo = function() {
      var modifyInfos = $scope.$parent.modifyInfos;
      if (!self.checkModifyUserInfo(modifyInfos)) {
        return;
      }
      $scope.showLoad()
      CourseService.updateModifyInfo({
        targetId : $scope.course.id
      }, function(data) {
        $scope.hideLoad();
        if (data.error) {
          $scope.toast(data.error.message);
          return;
        }
        if (true == data) {
          self.goToPay();
        }
      });
    };

    this.getModifyUserInfo = function(success) {
      $scope.$parent.close = function() {
        self.dialog.dialog("hide");
      }

      CourseService.getModifyInfo({}, function(data) {

        if(true != data["buy_fill_userinfo"]) {
          success();
          return
        }

        $scope.$parent.modifyInfos = data["modifyInfos"];
        if (data["modifyInfos"].length > 0) {
          self.dialog = $(".ui-dialog");
          self.dialog.dialog("show");
        } else {
          success();
        }
      });
    };

    $scope.vipLeand = function() {
      self.vipLeand(function() {
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
      });
    }

    $scope.joinCourse = function() {

      var course = $scope.course;
      if ("periodic" == course.type) {
        var currentDate = new Date(course.now).getTime();
        var endData = new Date(course.endTime).getTime();

        if (currentDate > endData) {
          alert("课程已结束");
          return
        }
      }

      self.join(function() {
        self.goToPay();
      });
    }

    $scope.favoriteCourse = function() {

      self.favoriteCourse(function() {
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
      });
    };

    $scope.shardCourse = function() {
      var about = $scope.course.about;
      if (about.length > 20) {
        about = about.substring(0, 20);
      }
      cordovaUtil.share(
        app.host + "/course/" + $scope.course.id, 
        $scope.course.title, 
        about, 
        $scope.course.largePicture
      );      
    }

    $scope.continueLearnCourse = function() {

      var course = $scope.course;
      if ("periodic" != course.type) {
        $scope.$root.$emit("continueLearnCourse", {});
        return;
      }

      var currentDate = new Date(course.now).getTime();
      var endData = new Date(course.endTime).getTime();

      if (currentDate > endData) {
        return "课程结束";
      }

      var startData = new Date(course.startTime).getTime();
      if (currentDate >= startData) {
        $scope.$root.$emit("continueLearnCourse", {});
        return;
      }

      alert("请等待开课!");
    };
}

app.controller('MoocCourseLessonController', ['$scope', '$stateParams', 'LessonService', '$state', 'cordovaUtil', MoocCourseLessonController]);
function MoocCourseLessonController($scope, $stateParams, LessonService, $state, cordovaUtil)
{
  var self = this;
  $scope.loading = true;
  this.loadLessons = function() {
      LessonService.getCourseLessons({
        courseId : $stateParams.courseId,
        token : $scope.token
      }, function(data) {
        $scope.loading = false;
        $scope.lessons = data.lessons;
        $scope.learnStatuses = data.learnStatuses;

        console.log($scope.lastLearnStatusIndex);
        for( index in data.learnStatuses ) {
            $scope.lastLearnStatusIndex = index;
        }
        
        self.continueLearnLesson();
      });
    }

    this.continueLearnLesson = function() {
      $scope.$root.$on("continueLearnCourse", function(event, data) {

          if (! $scope.lessons || $scope.lessons.length == 0) {
            alert("该课程没有可观看课时");
            return;
          }

          if (! $scope.lastLearnStatusIndex || $scope.lastLearnStatusIndex <= 0) {
            $scope.lastLearnStatusIndex = $scope.lessons[0].id;
          }
          
          var continueLesson =  self.findLessonById($scope.lastLearnStatusIndex);
          if (! continueLesson) {
            alert("还没有开始学习");
            return
          }
          $scope.learnLesson(continueLesson);
        });
    };

    this.findLessonById = function(lessonId) {
      var lessons = $scope.lessons;
      for (var i = 0; i < lessons.length; i++) {
        if (lessonId == lessons[i].id) {
          return lessons[i];
        }
      };

      return null;
    };

    this.createLessonIds = function() {
      var index = 0;
      var lessonIds = [];
      var lessons = $scope.lessons;
      for (var i = 0; i < lessons.length; i++) {
        if ("lesson" == lessons[i].itemType) {
          lessonIds[index++] = lessons[i].id;
        }
      };

      return lessonIds;
    };

    //lesson.free=1 is free
    $scope.learnLesson = function(lesson) {

      var course = $scope.course;
      if ("periodic" == course.type) {
        var currentDate = new Date(course.now).getTime();
        var endData = new Date(course.endTime).getTime();
        if (currentDate > endData) {
          alert("课程已结束，请等待下次开课");
          return;
        }
        
        var startData = new Date(course.startTime).getTime();
        if (currentDate < startData) {
          alert("请等待开课");
          return;
        }
      }

      if (! $scope.member && 1 != lesson.free) {
        alert("请先加入学习");
        return;
      }

      if ("lesson" != lesson.itemType) {
        return;
      }

      if (lesson.type == "flash" || "qqvideo" == lesson.mediaSource) {
        alert("客户端暂不支持该课时类型，敬请期待新版");
        return;
      }
      cordovaUtil.learnCourseLesson(lesson.courseId, lesson.id, self.createLessonIds());     
    }

    $scope.getLessonBoxStyle = function($index) {

      var style = "";
      $prevItem = $scope.lessons[$index -1];
      $nextItem = $scope.lessons[$index +1];

      var isNoTop = false, isNoBottom = false;
      if (!$prevItem || 'chapter' == $prevItem.itemType) {
        style += " no-top";
        isNoTop = true;
      }

      if (! $nextItem || 'lesson' != $nextItem.itemType) {
        style += " no-bottom";
        isNoBottom = true;
      }

      if (isNoTop && isNoBottom) {
        style = "hidden";
      }

      return style;
    }

    this.loadLessons();
    $scope.$on("$destroy", function(event, data) {
        $scope.$root.$on("continueLearnCourse", null);
    });
}