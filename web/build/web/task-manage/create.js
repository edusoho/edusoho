webpackJsonp(["web/task-manage/create"],[
/* 0 */
/***/ function(module, exports) {

	'use strict';

	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

	var Editor = function () {
	    function Editor($modal) {
	        _classCallCheck(this, Editor);

	        var $editor = $modal.find('#task-editor');
	        this.mode = $editor.data('editorMode');
	        this.elem = $modal;
	        this.type = $editor.data('editorType');
	        this.step = 1;
	        this.validator = null;
	        this.loaded = false;
	        this._init();
	        this._initEvent();
	        this._contentUrl = '';
	        this._saveUrl = $editor.data('saveUrl');
	    }

	    _createClass(Editor, [{
	        key: '_initEvent',
	        value: function _initEvent() {
	            $(this.elem).on('click', '#course-tasks-next', this._onNext.bind(this));
	            $(this.elem).on('click', '#course-tasks-prev', this._onPrev.bind(this));
	            $(this.elem).on('click', '.js-course-tasks-item', this._onSetType.bind(this));
	            $(this.elem).on('click', '#course-tasks-submit', this._onSave.bind(this));
	        }
	    }, {
	        key: '_init',
	        value: function _init() {
	            if (this.mode === 'edit') {
	                this._contentUrl = $("#task-editor").data('editorStep2Url');
	                this.step = 2;
	                this._switchPage();
	            }
	        }
	    }, {
	        key: '_onNext',
	        value: function _onNext(e) {
	            if (this.step >= 3) {
	                return;
	            }
	            this.step++;
	            this._switchPage();
	        }
	    }, {
	        key: '_onPrev',
	        value: function _onPrev() {
	            if (this.step <= 1 || this.mode === 'edit' && this.step <= 2) {
	                return;
	            }
	            this.step--;
	            this._switchPage();
	        }
	    }, {
	        key: '_onSetType',
	        value: function _onSetType(e) {
	            var $this = $(e.currentTarget).addClass('active');
	            $this.siblings().removeClass('active');
	            $('#course-tasks-next').removeAttr('disabled');
	            var type = $this.find('a').data('type');
	            $('[name="mediaType"]').val(type);
	            this._contentUrl = $this.find('a').data('contentUrl');
	            if (this.type !== type) {
	                this.loaded = false;
	                this.type = type;
	            }
	        }
	    }, {
	        key: '_onSave',
	        value: function _onSave() {
	            var self = this;
	            var postData = $('.js-hidden-data').map(function (index, node) {
	                var name = $(node).attr('name');
	                var value = $(node).val();
	                return { name: name, value: value };
	            }).filter(function (index, obj) {
	                return obj.value !== '';
	            }).get().concat($('#step2-form').serializeArray()).concat($("#step3-form").serializeArray()).concat([{ name: 'mediaType', value: this.type }]);

	            $.post(this._saveUrl, postData).done(function (response) {
	                self.elem.modal('hide');
	            }).fail(function (response) {});
	        }
	    }, {
	        key: '_switchPage',
	        value: function _switchPage() {
	            var _self = this;
	            var step = this.step;
	            if (step == 1) {
	                $("#task-type").show();
	                $(".js-step2-view").removeClass('active');
	                $(".js-step3-view").removeClass('active');
	            } else if (step == 2) {
	                $("#task-type").hide();
	                $(".js-step2-view").addClass('active');
	                $(".js-step3-view").removeClass('active');
	                !this.loaded && $('.tab-content').load(this._contentUrl, function () {
	                    _self._initStep2();
	                });
	            } else if (step == 3) {
	                $(".js-step3-view").addClass('active');
	                $(".js-step2-view").removeClass('active');
	                _self._initStep3();
	            }
	        }
	    }, {
	        key: '_initStep2',
	        value: function _initStep2() {
	            this.loaded = true;
	        }
	    }, {
	        key: '_initStep3',
	        value: function _initStep3() {}
	    }]);

	    return Editor;
	}();

	new Editor($('#modal'));

/***/ }
]);