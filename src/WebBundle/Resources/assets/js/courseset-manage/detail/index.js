import ReactDOM from 'react-dom';
import React from 'react';
import MultiInput from '../../../common/widget/multi-input';
import sortList from 'common/sortable';



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


function renderMultiGroupComponent(elementId,name){
  let datas = $('#'+elementId).data('init-value');
  console.log(datas);
  ReactDOM.render( <MultiInput dataSource= {datas}  outputDataElement={name}  sortable={true}/>,
    document.getElementById(elementId)
  );
}

renderMultiGroupComponent('course-goals','goals');
renderMultiGroupComponent('intended-students','audiences');


