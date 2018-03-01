import ReactDOM from 'react-dom';
import React from 'react';
import PersonaMultiInput from 'app/common/component/persona-multi-input';
import sortList from 'common/sortable';
import notify from 'common/notify';

ReactDOM.render( 
	<PersonaMultiInput 
		addable={true}  
		dataSource= {$('#course-teachers').data('init-value')}  
		outputDataElement='teachers' 
		inputName="ids[]"
		searchable={{enable:true,url:$('#course-teachers').data('query-url') + '?q='}} 
	/>,
	document.getElementById('course-teachers')
);

$('.js-btn-save').on('click', function(event){
	if($('input[name=teachers]').val() !== '[]'){
		$('#teachers-form').submit();
	}else{
		notify('warning', Translator.trans('course.manage.min_teacher_num_error_hint'));
	}
});