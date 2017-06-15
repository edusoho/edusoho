webpackJsonp(["app/js/classroom-manage/set-teachers/index"],[
/* 0 */
/***/ (function(module, exports) {

	function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
	
	import ReactDOM from 'react-dom';
	import React from 'react';
	import PersonaMultiInput from 'app/common/component/persona-multi-input';
	
	ReactDOM.render(React.createElement(PersonaMultiInput, _defineProperty({
	  showAddBtnGroup: false,
	  showDeleteBtn: false,
	  sortable: true,
	  showCheckbox: false,
	  addable: true,
	  outputDataElement: 'teachers',
	  searchable: { enable: false },
	  inputName: 'teacherIds[]',
	  dataSource: $('#classroom-manage-set-teachers').data('teachers')
	}, 'showAddBtnGroup', false)), document.getElementById('classroom-manage-set-teachers'));

/***/ })
]);