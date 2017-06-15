webpackJsonp(["app/js/task/plugin/question/index"],[
/* 0 */
/***/ (function(module, exports) {

	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	import postal from "postal";
	import QuestionForm from './form';
	import Question from './question';
	
	var QuestionPlugin = function () {
	  function QuestionPlugin() {
	    _classCallCheck(this, QuestionPlugin);
	
	    this.$element = $('.question-pane');
	    this.$list = this.$element.find('.question-list-block');
	    this.$detail = this.$element.find('.question-detail-block');
	    this.form = new QuestionForm();
	    this.question = null;
	    this.initEvent();
	  }
	
	  _createClass(QuestionPlugin, [{
	    key: 'initEvent',
	    value: function initEvent() {
	      var _this = this;
	
	      this.$element.on('click', '.js-redirect-question-detail', function (event) {
	        return _this.onRedirectQuestion(event);
	      });
	
	      var channel = postal.channel('task.plugin.question');
	
	      channel.subscribe('form.save', function (data, envelope) {
	        _this.$element.find('[data-role="list"]').prepend(data.html);
	        _this.$element.find('.empty-item').remove();
	      });
	
	      channel.subscribe('back-to-list', function () {
	        return _this.onBackList();
	      });
	    }
	  }, {
	    key: 'onRedirectQuestion',
	    value: function onRedirectQuestion(event) {
	      var $target = $(event.currentTarget);
	      var url = $target.data('url');
	      this.question = new Question(url);
	      this.$list.hide();
	      this.$detail.show();
	    }
	  }, {
	    key: 'onBackList',
	    value: function onBackList() {
	      this.question && this.question.destroy();
	      this.$list.show();
	      this.$detail.hide();
	    }
	  }]);
	
	  return QuestionPlugin;
	}();
	
	new QuestionPlugin();

/***/ })
]);