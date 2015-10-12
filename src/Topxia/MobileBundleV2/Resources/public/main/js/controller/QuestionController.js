app.controller('QuestionController', ['$scope', 'QuestionService', '$stateParams', QuestionController]);
app.controller('NoteController', ['$scope', 'NoteService', '$stateParams', NoteController]);

function QuestionController($scope, QuestionService, $stateParams)
{	
	var self = this;
	this.loadQuestion = function() {
		$scope.showLoad();
		QuestionService.getThread({
			courseId: $stateParams.courseId,
			threadId : $stateParams.threadId
		}, function(data) {
			$scope.thread = data;
			$scope.hideLoad();

			self.loadTeacherPost();
			self.loadTheadPost();
		});
	}
	
	this.loadTeacherPost = function() {
		QuestionService.getThreadTeacherPost({
			courseId: $stateParams.courseId,
			threadId : $stateParams.threadId
		}, function(data) {
			$scope.teacherPosts = data;
		});
	}

	this.loadTheadPost = function() {
		QuestionService.getThreadPost({
			courseId: $stateParams.courseId,
			threadId : $stateParams.threadId
		}, function(data) {
			$scope.threadPosts = data.data;
		});
	}

	self.loadQuestion();
}

function NoteController($scope, NoteService, $stateParams)
{	
	var self = this;
	this.loadNote = function() {
		$scope.showLoad();
		NoteService.getNote({
			noteId: $stateParams.noteId
		}, function(data) {
			$scope.note = data;
			$scope.hideLoad();
		});
	}

	self.loadNote();
}