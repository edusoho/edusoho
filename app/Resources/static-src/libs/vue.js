import Vue from 'vue/dist/vue.esm.js';
import { Button, Message, Modal } from '@codeages/design-vue';

Vue.use(Button)
Vue.use(Modal)

Vue.prototype.$confirm = Modal.confirm;
Vue.prototype.$message = Message;


window.Vue = Vue;

export default window.Vue;