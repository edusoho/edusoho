import ReactDOM from 'react-dom';
import React from 'react';
import PersonaMultiInput from 'app/common/component/persona-multi-input';

ReactDOM.render( <PersonaMultiInput replaceItem={true} sortable={true} addable={true} dataSource= {$('#classroom-manage-set-assistant').data('assistant')} outputDataElement='teachers' searchable={{enable:true,url:$('#classroom-manage-set-assistant').data('url') + "?q="}} />,
  document.getElementById('classroom-manage-set-assistant')
);