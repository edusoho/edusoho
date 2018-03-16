import ReactDOM from 'react-dom';
import React from 'react';
import PersonaMultiInput from 'app/common/component/persona-multi-input';

ReactDOM.render( <PersonaMultiInput 
  replaceItem={true} 
  sortable={false} 
  addable={true} 
  showCheckbox={false} 
  inputName='ids[]'
  dataSource= {$('#classroom-head-teacher').data('teacher')} outputDataElement='teachers' searchable={{enable:true,url:$('#classroom-head-teacher').data('url') + '?q='}} showDeleteBtn={false} />,
document.getElementById('classroom-head-teacher')
);