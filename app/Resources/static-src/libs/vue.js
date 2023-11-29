import Vue from 'vue/dist/vue.esm.js';
import { Icon, Button, Message, Modal } from '@codeages/design-vue';

Vue.use(Icon)
Vue.use(Button)
Vue.use(Modal)

Vue.prototype.$message = Message;
Vue.prototype.$info = Modal.info;
Vue.prototype.$success = Modal.success;
Vue.prototype.$error = Modal.error;
Vue.prototype.$warning = Modal.warning;
Vue.prototype.$confirm = Modal.confirm;
Vue.prototype.$destroyAll = Modal.destroyAll;


window.Vue = Vue;

export default window.Vue;