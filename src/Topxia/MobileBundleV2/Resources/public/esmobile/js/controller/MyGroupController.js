app.controller('MyGroupQuestionController', ['$scope', 'QuestionService', MyGroupQuestionController]);

function MyGroupNoteController($scope, NoteService)
{
	console.log("MyGroupNoteController");
	$scope.notes = [];
	$scope.canLoad = true;
	$scope.start = $scope.start || 0;
	
  	$scope.loadDataList = function(type) {
  		NoteService.getNoteList({
  			limit : 10,
			start: $scope.start,
			token : $scope.token
  		}, function(data) {
  			if (!data || data.length == 0 ) {
	    			$scope.canLoad = false;
	    		}

	    		$scope.notes = $scope.notes.concat(data);
	    		$scope.start += 10;

	    		$scope.$broadcast('scroll.infiniteScrollComplete');
  		});
  	}

  	$scope.canLoadMore = function() {
  		return $scope.canLoad;
  	};

  	$scope.loadMore = function(type){
  		$scope.loadDataList(type);
  	};
}

app.controller('MyGroupNoteController', ['$scope', 'NoteService', MyGroupNoteController]);

function MyGroupQuestionController($scope, QuestionService)
{
	console.log("MyGroupQuestionController");
	$scope.threads = [];
	$scope.canLoad = true;
	$scope.start = $scope.start || 0;
	
  	$scope.loadDataList = function(type) {
  		QuestionService.getCourseThreads({
  			limit : 10,
			start: $scope.start,
			type : type,
			token : $scope.token
  		}, function(data) {
  			if (!data || data.threads.length == 0 ) {
	    			$scope.canLoad = false;
	    		}

	    		$scope.threads = $scope.threads.concat(data.threads);
	    		$scope.start += data.limit;

	    		$scope.$broadcast('scroll.infiniteScrollComplete');
  		});
  	}

  	$scope.canLoadMore = function() {
  		return $scope.canLoad;
  	};

  	$scope.loadMore = function(type){
  		$scope.loadDataList(type);
  	};
}