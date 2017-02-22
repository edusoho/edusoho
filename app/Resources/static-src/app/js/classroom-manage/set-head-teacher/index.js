import ReactDOM from 'react-dom';
import React from 'react';
import PersonaMultiInput from 'app/common/component/persona-multi-input';

ReactDOM.render( <PersonaMultiInput sortable={false} addable={true} dataSource= {$('#classroom-head-teacher').data('teacher')} outputDataElement='teachers' searchable={{enable:true,url:$('#classroom-head-teacher').data('url') + "?q="}} />,
  document.getElementById('classroom-head-teacher')
);