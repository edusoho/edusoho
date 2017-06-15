webpackJsonp(["app/js/testpaper-manage/questions/index"],[
/* 0 */
/***/ (function(module, exports) {

	import Emitter from 'common/es-event-emitter';
	import 'jquery-sortable';
	import BatchSelect from '../../../common/widget/batch-select';
	import QuestionOperate from '../../../common/component/question-operate';
	import QuestionManage from './manage';
	
	var $testpaperItemsManager = $('#testpaper-items-manager');
	new QuestionOperate($testpaperItemsManager, $("#modal"));
	new QuestionManage($testpaperItemsManager);
	new BatchSelect($testpaperItemsManager);

/***/ })
]);