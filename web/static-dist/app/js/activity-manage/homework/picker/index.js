webpackJsonp(["app/js/activity-manage/homework/picker/index"],[
/* 0 */
/***/ (function(module, exports) {

	import QuestionPicker from 'app/common/component/question-picker';
	import BatchSelect from 'app/common/widget/batch-select';
	import SelectLinkage from 'app/js/question-manage/widget/select-linkage.js';
	
	var $questionPickerBody = $('#question-picker-body', window.parent.document);
	new QuestionPicker($questionPickerBody, $('#step2-form'));
	new BatchSelect($questionPickerBody);
	
	new SelectLinkage($('[name="courseId"]', window.parent.document), $('[name="lessonId"]', window.parent.document));

/***/ })
]);