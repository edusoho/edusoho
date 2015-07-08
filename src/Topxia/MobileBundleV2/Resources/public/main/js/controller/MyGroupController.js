function MyGroupBaseController($scope, serviceCallBack) {

  var self = this;
  this.limit = 10;
  $scope.data = [];
  $scope.canLoad = true;
  $scope.start = $scope.start || 0;

  this.loadDataList = function(type) {
      serviceCallBack({
        limit : self.limit,
        start: $scope.start,
        type : type
      }, function(data) {
        
        var length  = data ? data.data.length : 0;
        if (!data || length == 0 || length < self.limit) {
            $scope.canLoad = false;
          }

          $scope.data = $scope.data.concat(data.data);
          $scope.start += self.limit;
      });
    }
}

function MyGroupNoteController($scope, NoteService, cordovaUtil, $state)
{
      console.log("MyGroupNoteController");
      var self = this;
      this.__proto__ = new MyGroupBaseController($scope, NoteService.getNoteList);

    $scope.canLoadMore = function() {
      return $scope.canLoad;
    };

    $scope.loadMore = function(){
      self.loadDataList();
    };

     this.loadDataList();
}

function MyGroupQuestionController($scope, QuestionService)
{
  console.log("MyGroupQuestionController");
      this.__proto__ = new MyGroupBaseController($scope, QuestionService.getCourseThreads);
  
    $scope.canLoadMore = function() {
      return $scope.canLoad;
    };

    $scope.loadMore = function(){
      self.loadDataList("question");
    };

     this.loadDataList("question");
}

function MyGroupThreadController($scope, QuestionService)
{
  console.log("MyGroupThreadController");
  this.__proto__ = new MyGroupBaseController($scope, QuestionService.getCourseThreads);

    $scope.canLoadMore = function() {
      return $scope.canLoad;
    };

    $scope.loadMore = function(){
      self.loadDataList("discussion");
    };

   this.loadDataList("discussion");
}

app.controller('MyGroupQuestionController', ['$scope', 'QuestionService', MyGroupQuestionController]);
app.controller('MyGroupNoteController', ['$scope', 'NoteService', 'cordovaUtil', '$state', MyGroupNoteController]);
app.controller('MyGroupThreadController', ['$scope', 'QuestionService', MyGroupThreadController]);