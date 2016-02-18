app.controller('HomeWorkController', ['$scope', '$stateParams', 'HomeworkManagerService', 'AppUtil', HomeworkCheckController]);

function HomeworkCheckController($scope, $stateParams, HomeworkManagerService, AppUtil)
{
	function uncertainChoiceType(item) {
		return new choiceType(item);

	}

	function choiceType(item) {
		var self = this;

		this.getResultAnswer = function() {
			if (item.result && item.result.length > 0) {
				var answer = item.result.answer;
				return self.coverAnswer(answer);
			}

			return "未回答";
		}

		this.getIndexType = function(index) {
			switch (index) {
					case 0:
						return "A";
					case 1:
						return "B";
					case 2:
						return "C";
					case 3:
						return "D";
					case 4:
						return "E";
					case 5:
						return "F";
					case 6:
						return "G";
					case 7:
						return "H";
					case 8:
						return "I";
					case 9:
						return "J";
				}

				return "";
		};

		this.coverAnswer = function(answer) {
			var answerResult = "";
			for (var i = 0; i < answer.length; i++) {
				answerResult += self.getIndexType(i) + ",";
			};

			return answerResult;
		}

		this.getAnswer = function() {
			if (item.answer && item.answer.length > 0) {
				return self.coverAnswer(item.answer);
			}

			return "未回答";	
		};

		return {
			getAnswer : this.getAnswer,
			getIndexType : this.getIndexType,
			getResultAnswer : this.getResultAnswer,
		};
	};

	function essayType(item) {
		var self = this;
		this.getAnswer = function() {
			if (!item.answer || item.answer.length == 0) {
				return "";
			}

			return item.answer[0];	
		};

		this.getResultAnswer = function() {
			if (item.result) {
				var answer = item.result.answer;
				return self.getAnswer(answer);
			}

			return "no";
		}

		return {
			getAnswer : this.getAnswer,
			getResultAnswer : this.getResultAnswer
		};
	};

	var questionType = {
		single_choice : choiceType,
		essay : essayType,
		uncertain_choice : choiceType
	};

	$scope.loadHomeworkResult = function() {
		$scope.showLoad();
		HomeworkManagerService.showCheck({
			homeworkResultId : $stateParams.homeworkResultId
		}, function(data) {
			$scope.hideLoad();
			$scope.homeworkResult = data;
			$scope.items = data.items;
			$scope.currentQuestionIndex = 1;
			console.log(data);
		});
	};

	$scope.getResultAnswer = function(item) {
		var type = questionType[item.type];
		return type(item).getResultAnswer();
	};

	$scope.getItemAnswer = function(item) {
		var type = questionType[item.type];
		return type(item).getAnswer();
	}

	$scope.getItemStem = function(index, type) {
		var typeStr = "";
		switch (type) {
			case "single_choice":
				typeStr = "单选题";
				break;
			case "determine":
				typeStr = "判断题";
				break;
			case "essay":
				typeStr = "问答题";
				break;
			case "fill":
				typeStr = "填空题";
				break;
			case "material":
				typeStr = "材料题";
				break;
			case "uncertain_choice":
				typeStr = "不定项题";
				break;
			case "choice":
				typeStr = "多选题";
		}

		return AppUtil.formatString("%1, (%2)", index + 1, typeStr);
	}

	$scope.questionItemChange = function() {
		$scope.$apply(function() {
			$scope.currentQuestionIndex = $scope.scrollIndex + 1;
		});
	}

	$scope.getItemView = function(item) {
		var type = item.type;
		if (type.indexOf('choice')!= -1) {
			type = "choice";
		}
		return "view/homework_" + type + "_view.html";
	}

	$scope.getItemIndex = function(item, index) {
		var type = questionType[item.type];
		return type(item).getIndexType(index);
	}

	$scope.getFillQuestionItem = function(item) {
		var items = [], answer = item.answer;
		for (var i = 0; i < answer.length; i++) {
			items[i] = AppUtil.formatString("填写空(%1)答案", i + 1);
		};

		return items;
	}
}