import ReactDOM from 'react-dom';
import React from 'react';
import MultiInput from 'app/common/component/multi-input';
import sortList from 'common/sortable';

CKEDITOR.replace('summary', {
  allowedContent: true,
  toolbar: 'Detail',
  filebrowserImageUploadUrl: $('#courseset-summary-field').data('imageUploadUrl')
});

$('#courseset-submit').click(function (evt) {
  console.log($('#courseset-detail-form').serializeArray());
  $(evt.currentTarget).button('loading');
  $('#courseset-detail-form').submit();
});


function renderMultiGroupComponent(elementId, name) {
  let datas = $('#' + elementId).data('init-value');
  ReactDOM.render(<MultiInput
    blurIsAdd={true}
    sortable={true}
    dataSource={datas}
    inputName={name + "[]"}
    outputDataElement={name}
  />,
    document.getElementById(elementId)
  );
}

renderMultiGroupComponent('course-goals', 'goals');
renderMultiGroupComponent('intended-students', 'audiences');


