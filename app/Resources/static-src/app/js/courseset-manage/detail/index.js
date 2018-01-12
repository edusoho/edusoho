import ReactDOM from 'react-dom';
import React from 'react';
import MultiInput from 'app/common/component/multi-input';
import postal from 'postal';

class detail {
  constructor() {
    this.init();
  }

  init() {
    this.initCkeditor();
    this.renderMultiGroupComponent('course-goals', 'goals');
    this.renderMultiGroupComponent('intended-students', 'audiences');
    this.submitForm();
  }

  initCkeditor() {
    CKEDITOR.replace('summary', {
      allowedContent: true,
      toolbar: 'Detail',
      fileSingleSizeLimit: app.fileSingleSizeLimit,
      filebrowserImageUploadUrl: $('#courseset-summary-field').data('imageUploadUrl')
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

  submitForm() {
    $('#courseset-submit').click((event) => {
      this.publishAddMessage();
      $(event.currentTarget).button('loading');
      $('#courseset-detail-form').submit();
    });
  }

  publishAddMessage() {
    postal.publish({
      channel: "courseInfoMultiInput",
      topic: "addMultiInput",
    });
  }
}

new detail();