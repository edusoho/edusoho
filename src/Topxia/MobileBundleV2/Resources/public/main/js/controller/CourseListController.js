app.controller('CourseListController', ['$scope', '$stateParams', '$state', 'CourseUtil', 'CourseService', 'CategoryService', CourseListController]);
function CourseListController($scope, $stateParams, $state, CourseUtil, CourseService, CategoryService)
{
    this.getTypeName = function(name, types) {

      var defaultName = "全部分类";
      if (!name || !types) {
        return defaultName;
      }

      for (var i = types.length - 1; i >= 0; i--) {
        if (name == types[i].type) {
          defaultName = types[i].name;
          break;
        }
      };

      return defaultName;
    }

    $scope.courseListSorts = CourseUtil.getCourseListSorts();
    $scope.courseListTypes = CourseUtil.getCourseListTypes();
    $scope.categoryTab = {
      category : "分类",
      type : this.getTypeName($stateParams.type, $scope.courseListTypes),
      sort : "综合排序",
    };

    $scope.canLoad = true;
    $scope.start = $scope.start || 0;

    console.log("CourseListController");
      $scope.loadMore = function(successCallback){
        if (! $scope.canLoad) {
            return;
        }
        setTimeout(function() {
            $scope.loadCourseList($stateParams.sort, successCallback);
        }, 200);
      };

      $scope.loadCourseList = function(sort, successCallback) {
             $scope.showLoad();
              CourseService.searchCourse({
                limit : 10,
                start: $scope.start,
                categoryId : $stateParams.categoryId,
                sort : sort,
                type : $stateParams.type
              }, function(data) {
                        $scope.hideLoad();
                        if (successCallback) {
                          successCallback();
                        }
                        var length  = data ? data.data.length : 0;
                        if (!data || length == 0 || length < 10) {
                            $scope.canLoad = false;
                        }

                        $scope.courses = $scope.courses || [];
                        for (var i = 0; i < length; i++) {
                          $scope.courses.push(data.data[i]);
                        };

                        $scope.start += data.limit;
              });
      }

      CategoryService.getCategorieTree(function(data) {
        $scope.categoryTree = data;
      });

      $scope.selectType = function(item) {
             $scope.$emit("closeTab", {});
             $scope.categoryTab.type = item.name;
             clearData();
             $stateParams.type  = item.type;
             setTimeout(function(){
                $scope.loadCourseList($scope.sort);
             }, 100);
      }

      function clearData() {
                $scope.canLoad = true;
                $scope.start = 0;
                $scope.courses = null;
      }

      $scope.selectSort = function(item) {
        $scope.$emit("closeTab", {});
        $scope.categoryTab.sort = item.name;
        $scope.sort = item.type;
        clearData();
        setTimeout(function(){
            $scope.loadCourseList(item.type);
         }, 100);
      }

      $scope.onRefresh = function() {
        clearData();
        $scope.loadCourseList($scope.sort);
      }

      $scope.categorySelectedListener = function(category) {
             $scope.$emit("closeTab", {});
             $scope.categoryTab.category = category.name;
             clearData();
             $stateParams.categoryId  =category.id;
             $scope.loadCourseList($scope.sort);
      }

      $scope.loadCourseList();
}