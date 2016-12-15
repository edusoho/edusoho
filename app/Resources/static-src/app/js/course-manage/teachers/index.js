import ReactDOM from 'react-dom';
import React from 'react';
import PersonaMultiInput from '../../../common/Component/persona-multi-input';
import sortList from 'common/sortable';

ReactDOM.render( <PersonaMultiInput addable={true}  dataSource= {$('#course-teachers').data('init-value')}  outputDataElement='teachers' searchable={{enable:true,url:"/course/273/manage/teachersMatch?q={{query}}"}} />,
  document.getElementById('course-teachers')
);
