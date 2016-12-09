import { TabChange, publishCourse } from '../help';
import ReactDOM from 'react-dom';
import React from 'react';
import MultiGroup from '../../../common/widget/multi-group';
import sortList from 'common/sortable';


let items = [{
  id:1,
  value: '这是内容',
  checked: true,
  sqe: 1,
}];

ReactDOM.render( <MultiGroup items = {items} />,
  document.getElementById('course-goals')
);

// function renderMultiGroupComponent(elementId){
//   ReactDOM.render( <MultiGroup datas = {items} fieldName={$("#"+elementId).data('field-name')} sortable={true}/>,
//     document.getElementById(elementId)
//   );
// }

// console.log($("#course-goals").data("init-value"));

// renderMultiGroupComponent('course-goals');
// renderMultiGroupComponent('intended-students', $('#intended-students').data('init-value'));


// sortList({
//   element: ".sortable-list",
//   itemSelector: 'li',
// });

_initDatePicker('#expiryStartDate');
_initDatePicker('#expiryEndDate');
TabChange();
publishCourse();

CKEDITOR.replace('summary', {
  allowedContent: true,
  toolbar: 'Detail',
  filebrowserImageUploadUrl: $('#summary').data('imageUploadUrl')
});

let $form = $('#course-info-form');
let validator = $form.validate({
  onkeyup: false,
  groups: {
    date: 'expiryStartDate expiryEndDate'
  },
  rules: {
    title: {
      required: true
    },
    expiryDays: {
      required: '#expiryByDays:checked',
      digits: true
    },
    expiryStartDate: {
      required: '#expiryByDate:checked',
      date: true,
      before: '#expiryEndDate'
    },
    expiryEndDate: {
      required: '#expiryByDate:checked',
      date: true,
      after: '#expiryStartDate'
    }
  },
  messages: {
    title: Translator.trans('请输入教学计划课程标题'),
    expiryDays: Translator.trans('请输入学习有效期'),
    expiryStartDate: {
      required: Translator.trans('请输入开始日期'),
      before: Translator.trans('开始日期应早于结束日期')
    },
    expiryEndDate: {
      required: Translator.trans('请输入结束日期'),
      after: Translator.trans('结束日期应晚于开始日期')
    }
  }
});

$.validator.addMethod(
  "before",
  function(value, element, params) {
    if ($('input[name="expiryMode"]:checked').val() !== 'date') {
      return true;
    }
    return !!value || $(params).val() > value;
  },
  Translator.trans('开始日期应早于结束日期')
);

$.validator.addMethod(
  "after",
  function(value, element, params) {
    if ($('input[name="expiryMode"]:checked').val() !== 'date') {
      return true;
    }
    return !!value || $(params).val() < value;
  },
  Translator.trans('结束日期应晚于开始日期')
);

$('#course-submit').click(function(evt) {
  if (validator.form()) {
    $(evt.currentTarget).button('loading');
    $form.submit();
  }
});

function _initDatePicker($id) {
  let $picker = $($id);
  $picker.datetimepicker({
    format: 'yyyy-mm-dd',
    language: "zh",
    minView: 2, //month
    autoclose: true
  });
  $picker.datetimepicker('setStartDate', new Date());
}