import ReactDOM from 'react-dom';
import React from 'react';
import MultiInput from 'app/common/component/multi-input';
import postal from 'postal';

export default class detail {
  constructor() {
    this.init();
  }

  init() {
    this.initCkeditor();
    this.renderMultiGroupComponent('course-goals', 'goals');
    this.renderMultiGroupComponent('intended-students', 'audiences');
  }

  initCkeditor() {
    CKEDITOR.replace('summary', {
      allowedContent: true,
      toolbar: 'Detail',
      fileSingleSizeLimit: app.fileSingleSizeLimit,
      filebrowserImageUploadUrl: $('input[name="summary"]').data('imageUploadUrl')
    });
  }

  renderMultiGroupComponent(elementId, name) {
    let datas = $('#' + elementId).data('init-value');
    ReactDOM.render(<MultiInput
      blurIsAdd={true}
      sortable={true}
      dataSource={datas}
      inputName={name + "[]"}
      outputDataElement={name} />,
      document.getElementById(elementId)
    );
  }

  publishAddMessage() {
    postal.publish({
      channel: "courseInfoMultiInput",
      topic: "addMultiInput",
    });
  }
}