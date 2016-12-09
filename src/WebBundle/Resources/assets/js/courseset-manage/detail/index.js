import ReactDOM from 'react-dom';
import React from 'react';
import MultiGroup from '../../../common/widget/multi-group';
import sortList from 'common/sortable';

class DetailEditor {
  constructor() {
    this.init();
  }

  init() {
    this.renderMultiGroupComponent('course-goals');
    this.renderMultiGroupComponent('intended-students');

    CKEDITOR.replace('summary', {
      allowedContent: true,
      toolbar: 'Detail',
      filebrowserImageUploadUrl: $('#summary').data('imageUploadUrl')
    });

    $('#courseset-submit').click(function(evt) {
      console.log($('#courseset-detail-form').serializeArray());
      $(evt.currentTarget).button('loading');
      $('#courseset-detail-form').submit();
    });
  }

  renderMultiGroupComponent(elementId){
    ReactDOM.render( <MultiGroup items = {$("#"+elementId).data("init-value")} fieldName={$("#"+elementId).data('field-name')} />,
      document.getElementById(elementId)
    );
  }

}

new DetailEditor();
