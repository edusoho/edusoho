import ReactDOM from 'react-dom';
import React from 'react';
import PersonaMultiInput from '../../../common/widget/persona-multi-input';
import sortList from 'common/sortable';

ReactDOM.render( <PersonaMultiInput addable={true}  dataSource= {$('#course-teachers').data('init-value')}  outputDataElement='teachers' searchable={{enable:true,url:$('#course-teachers').data('query-url') + "?q="}} />,
  document.getElementById('course-teachers')
);
