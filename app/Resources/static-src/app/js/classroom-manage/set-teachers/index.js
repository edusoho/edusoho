import ReactDOM from 'react-dom';
import React from 'react';
import PersonaMultiInput from 'app/common/component/persona-multi-input';

ReactDOM.render(<PersonaMultiInput 
  showAddBtnGroup = { false } 
  showDeleteBtn = { false }
  sortable = { true } 
  showCheckbox = { false } 
  addable = { true }
  outputDataElement = 'teachers'
  searchable = {{ enable: false}} 
  inputName = "teacherIds[]"
  dataSource = { $('#classroom-manage-set-teachers').data('teachers') }
  showAddBtnGroup = { false }/>,
document.getElementById('classroom-manage-set-teachers')
);