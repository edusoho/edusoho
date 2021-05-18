import Vue from 'vue/dist/vue.esm.js';

import { Menu, Button, Table, Select, Form, FormModel, DatePicker, Input, Modal, Col, Row } from 'ant-design-vue';

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
}

window.Vue = window.Vue || Vue;

export default window.Vue;
