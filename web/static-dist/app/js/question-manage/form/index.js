webpackJsonp(["app/js/question-manage/form/index"],[
/* 0 */
/***/ (function(module, exports) {

	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	import QuestionFormBase from '../type/form-base';
	import Choice from '../type/question-choice';
	import SingleChoice from '../type/question-single-choice';
	import UncertainChoice from '../type/question-uncertain-choice';
	import Determine from '../type/question-determine';
	import Fill from '../type/question-fill';
	import Essay from '../type/question-essay';
	import Material from '../type/question-material';
	import SelectLinkage from '../widget/select-linkage.js';
	
	var QuestionCreator = function () {
	  function QuestionCreator() {
	    _classCallCheck(this, QuestionCreator);
	  }
	
	  _createClass(QuestionCreator, null, [{
	    key: 'getCreator',
	    value: function getCreator(type, $form) {
	      switch (type) {
	        case 'single_choice':
	          QuestionCreator = new SingleChoice($form);
	          break;
	        case 'uncertain_choice':
	          QuestionCreator = new UncertainChoice($form);
	          break;
	        case 'choice':
	          QuestionCreator = new Choice($form);
	          break;
	        case 'determine':
	          QuestionCreator = new Determine($form);
	          break;
	        case 'essay':
	          QuestionCreator = new Essay($form);
	          break;
	        case 'fill':
	          QuestionCreator = new Fill($form);
	          break;
	        case 'material':
	          QuestionCreator = new Material($form);
	          break;
	        default:
	          QuestionCreator = new QuestionFormBase($form);
	          QuestionCreator.initTitleEditor();
	          QuestionCreator.initAnalysisEditor();
	      }
	
	      return QuestionCreator;
	    }
	  }]);
	
	  return QuestionCreator;
	}();
	
	var $form = $('[data-role="question-form"]');
	var type = $('[data-role="question-form"]').find('[name="type"]').val();
	
	QuestionCreator.getCreator(type, $form);
	
	new SelectLinkage($('[data-role="courseId"]'), $('[data-role="lessonId"]'));

/***/ })
]);