app.controller('CourseController', ['$scope', '$stateParams', 'CourseService', 'AppUtil', '$state', 'cordovaUtil', CourseController]);
app.controller('CourseDetailController', ['$scope', '$stateParams', 'CourseService', CourseDetailController]);
app.controller('CourseSettingController', ['$scope', '$stateParams', 'CourseService', 'ClassRoomService', CourseSettingController]);

function CourseReviewController($scope, $stateParams, CourseService, ClassRoomService)
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

  this.loadCourseReviews = function(callback) {
    CourseService.getReviews({
      start : $scope.start,
      limit : 50,
      courseId : $stateParams.targetId
    }, callback);
  };

  this.loadClassRoomReviews = function(callback) {
    ClassRoomService.getReviews({
      start : $scope.start,
      limit : 50,
      classRoomId : $stateParams.targetId
    }, callback);
  };

  this.initTargetService = function(targetType) {
    if (targetType == "course") {
      self.targetInfoService = this.loadCourseReviewInfo;
      self.targetService = this.loadCourseReviews;
    } else if (targetType == "classroom") {
      self.targetInfoService = this.loadClassRoomReviewInfo;
      self.targetService = this.loadClassRoomReviews;
    }
  };

  this.loadReviews = function() {
    self.targetService(function(data) {
      var length  = data ? data.data.length : 0;
      if (!data || length == 0 || length < 50) {
          $scope.canLoad = false;
      }

      $scope.reviews = $scope.reviews || [];
      for (var i = 0; i < length; i++) {
        $scope.reviews.push(data.data[i]);
      };

      $scope.start += data.limit;
    });
  };

  this.loadCourseReviewInfo = function() {
    CourseService.getCourseReviewInfo({
      courseId : $stateParams.targetId
    }, function(data) {
      $scope.reviewData = data;
    });
  }

  this.loadClassRoomReviewInfo = function() {
    ClassRoomService.getReviewInfo({
      classRoomId : $stateParams.targetId
    }, function(data) {
      $scope.reviewData = data;
    });
  }

  $scope.loadReviewResult = function() {

    self.targetInfoService();
    self.loadReviews();
  }
  
  this.initTargetService($stateParams.targetType);
}

function CourseSettingController($scope, $stateParams, CourseService, $window)
{
  $scope.isLearn = $stateParams.isLearn;
  $scope.exitLearnCourse = function(reason) {
    $scope.showLoad();
    CourseService.unLearnCourse({
      reason : reason,
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

app.controller('CourseToolController', ['$scope', '$stateParams', 'OrderService', 'CourseService', 'UserService', 'cordovaUtil', '$state', CourseToolController]);

function BaseToolController($scope, OrderService, UserService, cordovaUtil)
{
  var self = this;

  this.payCourse = function(price, targetType, targetId) {
      OrderService.createOrder({
        payment : "alipay",
        payPassword : "",
        totalPrice : price,
        couponCode : "",
        targetType : targetType,
        targetId : targetId
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

  this.vipLeand = function(vipLevelId, callback) {
    if ($scope.user == null) {
      cordovaUtil.openWebView(app.rootPath + "#/login/course");
      return;
    }
    
    $scope.showLoad();
    UserService.getUserInfo({
      userId : $scope.user.id,
    }, function(data) {
      $scope.hideLoad();
      if (data.vip == null || data.vip.levelId < vipLevelId) {
        cordovaUtil.openWebView(app.rootPath + "#/viplist");
      } else {
        callback();
      }
    });
  }

  this.join = function(callback) {
      if ($scope.user == null) {
        cordovaUtil.openWebView(app.rootPath + "#/login/course");
        return;
      }

      callback();
    }

  this.favoriteCourse = function(callback) {
    if ($scope.user == null) {
      cordovaUtil.openWebView(app.rootPath + "#/login/course");
      return;
    }

    callback();
  }

  $scope.getVipTitle = function(vipLevelId) {
      var vipLevels = $scope.vipLevels;
      for (var i = 0; i < vipLevels.length; i++) {
        var level = vipLevels[i];
        if (level.id == vipLevelId) {
          return level.name;
        }
      };
      
      return "";
  }

  this.filterContent = function(content, limit) {

    content = content.replace(/<\/?[^>]*>/g,'');
    content = content.replace(/[\r\n\s]+/g,'');
    if (content.length > limit) {
         content = content.substring(0, limit);
      }
      
      return content;
  }

  $scope.isCanShowVip = function(vipLevelId) {
    if (vipLevelId <= 0) {
      return false;
    }
    return $scope.vipLevels.length > 0;
  }
}

function CourseToolController($scope, $stateParams, OrderService, CourseService, UserService, cordovaUtil, $state)
{
    this.__proto__ = new BaseToolController($scope, OrderService, UserService, cordovaUtil);
    var self = this;

    this.goToPay = function() {
      var course = $scope.course;
      var price = course.price;
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
      self.vipLeand($scope.course.vipLevelId, function() {
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

      cordovaUtil.share(
        app.host + "/course/" + $scope.course.id, 
        $scope.course.title, 
        self.filterContent(about, 20), 
        $scope.course.largePicture
      );      
    }

    $scope.continueLearnCourse = function() {
      $scope.$root.$emit("continueLearnCourse", {});
    };
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
      $scope.teachers = data.course.teachers;

      if (data.member) {
        var progress = data.course.lessonNum == 0 ? 0 : data.member.learnedNum / data.course.lessonNum;
        $scope.learnProgress = ((progress * 100).toFixed(2)) + "%" ;
      }

      $scope.courseView = app.viewFloder + (data.member ? "view/course_learn.html" : "view/course_no_learn.html");
      $scope.hideLoad();
    });

    $scope.loadReviews = function(){
      CourseService.getReviews({
        courseId : $stateParams.courseId,
        limit : 1
      }, function(data) {
        $scope.reviewCount = data.total;
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

    $scope.isCanShowConsultBtn = function() {
      if (! $scope.user) {
        return false;
      }
      
      if ("classroom" == $scope.course.source) {
        return false;
      }

      if (!$scope.teachers || $scope.teachers.length == 0) {
        return false;
      }

      return true;
    };

    $scope.consultCourseTeacher = function() {
      if (!$scope.teachers || $scope.teachers.length == 0) {
        alert("该课程暂无教师");
        return;
      }

      var userId = $scope.teachers[0].id;
      cordovaUtil.startAppView("courseConsult", { userId : userId });
    };
}

app.controller('ClassRoomController', ['$scope', '$stateParams', 'ClassRoomService', 'AppUtil', '$state', 'cordovaUtil', 'ClassRoomUtil', ClassRoomController]);
app.controller('ClassRoomCoursesController', ['$scope', '$stateParams', 'ClassRoomService', '$state', ClassRoomCoursesController]);
app.controller('ClassRoomToolController', ['$scope', '$stateParams', 'OrderService', 'ClassRoomService', 'UserService', 'cordovaUtil', '$state', ClassRoomToolController]);

function ClassRoomToolController($scope, $stateParams, OrderService, ClassRoomService, UserService, cordovaUtil, $state)
{
  this.__proto__ = new BaseToolController($scope, OrderService, UserService, cordovaUtil);
    var self = this;

    $scope.signDate = new Date();
    this.goToPay = function() {
      var classRoom = $scope.classRoom;
      var price = classRoom.price;
      if (price <= 0) {
        self.payCourse(price, "classroom", $stateParams.classRoomId);
      } else {
        $state.go("coursePay", { targetId : $scope.classRoom.id, targetType : 'classroom' });
      }
    };

    $scope.sign = function() {
      if ($scope.signInfo && $scope.signInfo.isSignedToday) {
        $scope.toast("今天已经签到了!");
        return;
      }
      ClassRoomService.sign({
        classRoomId : $stateParams.classRoomId
      }, function(data) {
        if(data.error) {
          $scope.toast(data.error.message);
          return;
        }

        $scope.signInfo = data;
      });
    }

    $scope.joinClassroom = function() {
      self.join(function() {
        self.goToPay();
      });
    }

    $scope.getTodaySignInfo = function() {
      ClassRoomService.getTodaySignInfo({
        classRoomId : $stateParams.classRoomId
      }, function(data) {
        $scope.signInfo = data;
      });
    };

    $scope.shardClassRoom = function() {
      var about = $scope.classRoom.about;

      cordovaUtil.share(
        app.host + "/classroom/" + $scope.classRoom.id, 
        $scope.classRoom.title, 
        self.filterContent(about, 20), 
        $scope.classRoom.largePicture
      );
    };

    $scope.learnByVip = function() {
      self.vipLeand($scope.classRoom.vipLevelId, function() {
        ClassRoomService.learnByVip({
          classRoomId : $stateParams.classRoomId
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
}

function ClassRoomCoursesController($scope, $stateParams, ClassRoomService, $state)
{
  var self = this;

  this.loadClassRoomCourses = function() {
    $scope.loading = true;
    ClassRoomService.getClassRoomCourses({
      classRoomId : $stateParams.classRoomId
    }, function(data) {
      $scope.loading = false;
      
      if (data.error) {
        $scope.toast(data.error.message);
        return;
      }
      $scope.courses = data.courses;
      $scope.progressArray = data.progress;
    });
  };

  this.loadClassRoomCourses();
}

function ClassRoomController($scope, $stateParams, ClassRoomService, AppUtil, $state, cordovaUtil, ClassRoomUtil)
{
  var self = this;

  this.loadClassRoom = function() {
    $scope.showLoad();
    ClassRoomService.getClassRoom({
      id : $stateParams.classRoomId
    }, function(data) {
      $scope.ratingArray = AppUtil.createArray(5);
      $scope.vipLevels = data.vipLevels;
      $scope.member = data.member;
      $scope.isFavorited = data.userFavorited;
      $scope.discount = data.discount;

      $scope.classRoomView = app.viewFloder + (data.member ? "view/classroom_learn.html" : "view/classroom_no_learn.html");
      $scope.classRoom = ClassRoomUtil.filterClassRoom(data.classRoom);
      $scope.hideLoad();
    });
  };

  $scope.loadClassRoomDetail = function() {
    $scope.classRoomDetailContent = $scope.classRoom.about;
    $scope.$apply();
  }

  $scope.loadReviews = function(){
      ClassRoomService.getReviews({
        classRoomId : $stateParams.classRoomId,
        limit : 1
      }, function(data) {
        $scope.reviews = data.data;
      });
  };

  $scope.loadStudents = function() {
    ClassRoomService.getStudents({
      classRoomId : $stateParams.classRoomId,
      limit : 3
    }, function(data) {
      $scope.students = data.resources;
    });
  };

  $scope.loadTeachers = function() {
    ClassRoomService.getTeachers({
      classRoomId : $stateParams.classRoomId,
    }, function(data) {
      if (data && data.length > 1) {
        var length = data.length;
        for (var i = 2; i < length; i++) {
          data.pop();
        };
      }

      $scope.classRoom.teachers = data;
    });
  };

  $scope.unLearn = function(reason) {
      $scope.showLoad();
      ClassRoomService.unLearn({
        reason : reason,
        targetType : "classroom",
        classRoomId : $stateParams.classRoomId
      }, function(data) {
        $scope.hideLoad();
        if (! data.error) {
          window.location.reload();
        } else {
          $scope.toast(data.error.message);
        }
      });
    }

  this.loadClassRoom();
}

app.controller('CourseCardController', ['$scope', '$stateParams', 'CourseService', CourseCardController]);
function CourseCardController($scope, $stateParams, CourseService)
{

  CourseService.getCourse({
    courseId : $stateParams.courseId
  }, function(data) {
    $scope.course = data.course;
  });
}