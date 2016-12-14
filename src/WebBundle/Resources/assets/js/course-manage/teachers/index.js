import ReactDOM from 'react-dom';
import React from 'react';
import TeacherMultiInput from '../../../common/Component/teacher-multi-input';
import sortList from 'common/sortable';

ReactDOM.render( <TeacherMultiInput  dataSource= {$('#course-teachers').data('init-value')}  outputDataElement='teachers' searchable={{enable:true,url:"/course/273/manage/teachersMatch?q={{query}}"}}/>,
  document.getElementById('course-teachers')
);
