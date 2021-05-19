import Vue from 'vue/dist/vue.esm.js';

import { Menu, Button, Table, Select, Form, AutoComplete,
  FormModel, DatePicker, Input, Modal, Col, Row, Radio, Switch
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
}

window.Vue = window.Vue || Vue;

export default window.Vue;
