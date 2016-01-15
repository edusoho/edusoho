app.controller(
  'ClassRoomListController', 
  [
    '$scope', 
    '$stateParams', 
    '$state', 
    'CourseUtil', 
    'ClassRoomService', 
    'CategoryService', 
    'ClassRoomUtil',
     ClassRoomListController
  ]
);

function ClassRoomListController($scope, $stateParams, $state, CourseUtil, ClassRoomService, CategoryService, ClassRoomUtil)
{
    $scope.categoryTab = {
      category : "分类",
      type : "全部分类",
      sort : "综合排序",
    };

    $scope.canLoad = true;
    $scope.start = $scope.start || 0;

    console.log("ClassRoomListController");
      $scope.loadMore = function(successCallback){
            if (! $scope.canLoad) {
              return;
            }
           setTimeout(function() {
              $scope.loadClassRoomList($stateParams.sort, successCallback);
           }, 200);
         
      };

      $scope.loadClassRoomList = function(sort, successCallback) {
             $scope.showLoad();
              ClassRoomService.searchClassRoom({
                limit : 10,
                start: $scope.start,
                category : $stateParams.categoryId,
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

                $scope.classRooms = $scope.classRooms || [];
                for (var i = 0; i < length; i++) {
                  $scope.classRooms.push(ClassRoomUtil.filterClassRoom(data.data[i]));
                };

                $scope.start += data.limit;
              });
      }

      $scope.courseListSorts = CourseUtil.getClassRoomListSorts();

      CategoryService.getCategorieTree(function(data) {
        $scope.categoryTree = data;
      });

      $scope.selectType = function(item) {
             $scope.$emit("closeTab", {});
             $scope.categoryTab.type = item.name;
             clearData();
             $stateParams.type  = item.type;
             setTimeout(function(){
                $scope.loadClassRoomList($scope.sort);
             }, 100);
      }

      function clearData() {
        $scope.canLoad = true;
        $scope.start = 0;
        $scope.classRooms = null;
      }

      $scope.selectSort = function(item) {
        $scope.$emit("closeTab", {});
        $scope.categoryTab.sort = item.name;
        $scope.sort = item.type;
        clearData();
        setTimeout(function(){
            $scope.loadClassRoomList(item.type);
         }, 100);
      }

      $scope.onRefresh = function() {
        clearData();
        $scope.loadClassRoomList($scope.sort);
      }

      $scope.categorySelectedListener = function(category) {
             $scope.$emit("closeTab", {});
             $scope.categoryTab.category = category.name;
             clearData();
             $stateParams.type = null;
             $stateParams.categoryId  =category.id;
             $scope.loadClassRoomList($scope.sort);
      }

      $scope.loadClassRoomList();
}