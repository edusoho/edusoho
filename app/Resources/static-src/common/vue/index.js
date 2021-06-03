import Vue from 'vue/dist/vue.esm.js';
import _ from 'lodash';
import 'moment';

import { Menu, Button, Table, Select, Form, AutoComplete, Upload,
  FormModel, DatePicker, Input, Modal, Col, Row, Radio, Switch, Icon, Checkbox,
  Pagination, Spin, Popconfirm, Dropdown, Space, Descriptions, Tag, Tooltip,
  Divider, Message, Notification, Tabs, Tree, TimePicker, InputNumber, Breadcrumb, Empty
} from '@codeages/design-vue';
import Clipboard from 'v-clipboard';

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
  Vue.use(Space)
  Vue.use(Descriptions)
  Vue.use(Tag)
  Vue.use(Tooltip)
  Vue.use(Divider)
  Vue.use(Tree)
  Vue.use(Tabs)
  Vue.use(TimePicker)
  Vue.use(InputNumber)
  Vue.use(Breadcrumb)
  Vue.use(Empty)

  Vue.use(Clipboard)

  Vue.prototype.$message = Message;
  Vue.prototype.$notification = Notification;
  Vue.prototype.$info = Modal.info;
  Vue.prototype.$success = Modal.success;
  Vue.prototype.$error = Modal.error;
  Vue.prototype.$warning = Modal.warning;
  Vue.prototype.$confirm = Modal.confirm;
  Vue.prototype.$destroyAll = Modal.destroyAll;

  Message.config({
    top: `100px`
  });
}

if (!window.Vue) {
  Vue.filter('trans', (value) => {
    if (_.isObject(value)) {
      Translator.trans(value.text, value.options || {})
    } else if (_.isString(value)) {
      Translator.trans(value)
    }
  })

  Vue.prototype.$dateFormat = function(value, format = 'YYYY-MM-DD') {
    if (value == 0) {
      return '';
    }
    return moment(value * 1000).format(format)
  }
}

window.Vue = window.Vue || Vue;

export default window.Vue;
