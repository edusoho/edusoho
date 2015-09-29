app.controller('MyFavoriteCourseController', ['$scope', 'CourseService', 'CourseUtil', MyFavoriteCourseController]);
app.controller('MyFavoriteLiveController', ['$scope', 'CourseService', 'CourseUtil', MyFavoriteLiveController]);

function MyFavoriteBaseController($scope, CourseService, CourseUtil)
{
  var self = this;
  $scope.data  = CourseUtil.getFavoriteListTypes();

    this.loadDataList = function(type) {
      $scope.showLoad();
      var content = $scope.data[type];
      CourseService.getFavoriteCourse(
        content.url,
        {
          limit : 100,
        start: content.start
      }, function(data) {
            $scope.hideLoad();
            if (!data || data.data.length == 0) {
              content.canLoad = false;
            }

            content.data = content.data || [];
            content.data = content.data.concat(data.data);
            content.start += data.limit;

            if (data.total && content.start >= data.total) {
              content.canLoad = false;
            }
        }
      );
    }
}

function MyFavoriteCourseController($scope, CourseService, CourseUtil)
{
      console.log("MyFavoriteCourseController");
	this.__proto__ = new MyFavoriteBaseController($scope, CourseService, CourseUtil);

      var self = this;
      this.loadCourses = function() {
        self.loadDataList("course");
      }

      this.loadCourses();
}

function MyFavoriteLiveController($scope, CourseService, CourseUtil)
{
      console.log("MyFavoriteLiveController");
      this.__proto__ = new MyFavoriteBaseController($scope, CourseService, CourseUtil);

      var self = this;
      this.loadLiveCourses = function() {
        self.loadDataList("live");
      }

      this.loadLiveCourses();
}