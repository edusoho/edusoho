import Vue from 'vue/dist/vue.esm.js';
import _ from 'lodash';
import 'moment';

import { Menu, Button, Table, Select, Form, AutoComplete, Upload,
  FormModel, DatePicker, Input, Modal, Col, Row, Radio, Switch, Icon,
  Pagination, Spin, Popconfirm, Dropdown, Checkbox
} from 'ant-design-vue';

if (!window.Vue) {
  Vue.use(Menu)
  Vue.use(Button)
  Vue.use(Table)
  Vue.use(Select)
  Vue.use(Form)
  Vue.use(FormModel)
  Vue.use(DatePicker)
  Vue.use(Input)
  Vue.use(Modal)
  Vue.use(Col)
  Vue.use(Row)
  Vue.use(Radio)
  Vue.use(Switch)
  Vue.use(AutoComplete)
  Vue.use(Upload)
  Vue.use(Icon)
  Vue.use(Pagination)
  Vue.use(Spin)
  Vue.use(Popconfirm)
  Vue.use(Dropdown)
  Vue.use(Checkbox)
}

if (!window.Vue) {
  Vue.filter('trans', (value) => {
    if (_.isObject(value)) {
      Translator.trans(value.text, value.options || {})
    } else if (_.isString(value)) {
      Translator.trans(value)
    }
  })

  Vue.filter('YYYY-MM-DD', value => {
    return moment(value, 'YYYY-MM-DD')
  })

  Vue.filter('YYYY-MM-DD HH:ss', value => {
    return moment(value, 'YYYY-MM-DD HH:ss')
  })
}

window.Vue = window.Vue || Vue;

export default window.Vue;
