webpackJsonp(["app/js/classroom-manage/set-assistants/index"],[
/* 0 */
/***/ (function(module, exports) {

	import ReactDOM from 'react-dom';
	import React from 'react';
	import PersonaMultiInput from 'app/common/component/persona-multi-input';
	
	ReactDOM.render(React.createElement(PersonaMultiInput, {
	  sortable: true,
	  showCheckbox: false,
	  addable: true,
	  dataSource: $('#classroom-manage-set-assistant').data('assistants'), outputDataElement: 'teachers',
	  inputName: 'ids[]',
	  searchable: { enable: true, url: $('#classroom-manage-set-assistant').data('url') + "?q=" } }), document.getElementById('classroom-manage-set-assistant'));

/***/ })
]);