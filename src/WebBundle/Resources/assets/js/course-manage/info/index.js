import { TabChange, publishCourse } from '../help';
import ReactDOM from 'react-dom';
import React from 'react';
import MultiGroup from '../../../common/widget/multi-group';
import sortList from 'common/sortable';

import Select, { Option } from 'rc-select';

//测试数据
const Search = React.createClass({
  getInitialState() {
    return {
      disabled: false,
      data: [],
      value: undefined,
    };
  },

  onChange(value) {
    console.log('select ', value);
    this.setState({
      value,
    });
  },

  fetchData(value) {
    if (value) {
      fetch(value, (data) => {
        this.setState({
          data,
        });
      });
    } else {
      this.setState({
        data: [],
      });
    }
  },

  toggleDisabled() {
    this.setState({
      disabled: !this.state.disabled,
    });
  },

  render() {
    const data = this.state.data;
    let options;
    options = data.map((d) => {
      return <Option key={d.value}><i>{d.text}</i></Option>;
    });
    return (<div>
      <h2>force suggest</h2>
      <p>
        <button onClick={this.toggleDisabled}>toggle disabled</button>
      </p>
      <div>
        <Select
          labelInValue
          onSearch={this.fetchData}
          disabled={this.state.disabled}
          value={this.state.value}
          optionLabelProp="children"
          placeholder="placeholder"
          defaultActiveFirstOption
          style={{ width: 500 }}
          onChange={this.onChange}
          filterOption={false}
        >
          {options}
        </Select>
      </div>
    </div>);
  },
});
ReactDOM.render(<Search />, document.getElementById('test'));


ReactDOM.render( <MultiGroup items = {[]}  />,
  document.getElementById('course-objectives')
);
ReactDOM.render( <MultiGroup items = {[]}  />,
  document.getElementById('adapt-crowd')
);

sortList({
  element: ".sortable-list",
  itemSelector: 'li',
});

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