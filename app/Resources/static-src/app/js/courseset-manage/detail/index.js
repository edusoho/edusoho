import ReactDOM from 'react-dom';
import React from 'react';
import MultiInput from 'app/common/component/multi-input';
import postal from 'postal';

CKEDITOR.replace('summary', {
  allowedContent: true,
  toolbar: 'Detail',
  filebrowserImageUploadUrl: $('#courseset-summary-field').data('imageUploadUrl')
});

renderMultiGroupComponent('course-goals', 'goals');
renderMultiGroupComponent('intended-students', 'audiences');

$('#courseset-submit').click(function (evt) {
  publishAddMessage();
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

function publishAddMessage() {
  postal.publish({
    channel: "courseInfoMultiInput",
    topic: "addMultiInput",
  });
}