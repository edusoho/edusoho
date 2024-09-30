import Vue from 'vue/dist/vue.esm.js';
import 'moment';
import {Affix, Alert, Anchor, Button, Checkbox, Col, Collapse, Dropdown, Form, Icon, Input, InputNumber, LocaleProvider, Menu, Message, Modal, Radio, Row, Select, TreeSelect, Table, Tooltip} from '@codeages/design-vue';

Vue.use(Affix)
  .use(Alert)
  .use(Anchor)
  .use(Button)
  .use(Checkbox)
  .use(Col)
  .use(Collapse)
  .use(Dropdown)
  .use(Form)
  .use(Icon)
  .use(Input)
  .use(InputNumber)
  .use(LocaleProvider)
  .use(Menu)
  .use(Message)
  .use(Modal)
  .use(Radio)
  .use(Row)
  .use(Select)
  .use(TreeSelect)
  .use(Table)
  .use(Tooltip);

Message.config({
  getContainer: () => {
    return document.getElementsByClassName('ibs-vue')[0] || document.body;
  }
});

Vue.prototype.$message = Message;
Vue.prototype.$modal = Modal;
Vue.prototype.$info = Modal.info;
Vue.prototype.$success = Modal.success;
Vue.prototype.$error = Modal.error;
Vue.prototype.$warning = Modal.warning;
Vue.prototype.$confirm = Modal.confirm;
Vue.prototype.$destroyAll = Modal.destroyAll;

Vue.prototype.$dateFormat = function (value, format = 'YYYY-MM-DD') {
  if (value == 0) {
    return '';
  }
  return moment(value * 1000).format(format)
}

window.Vue = Vue;

export default window.Vue;