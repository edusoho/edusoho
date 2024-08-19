import { createApp, h } from 'vue';
import ManageInfo from './manage-info.vue';
import Antd from 'ant-design-vue';
import 'ant-design-vue/dist/antd.css';

const app = createApp({
  render() {
    return h(ManageInfo, {
      exercise: $('#app').data('exercise'),
    });
  }
});

// 使用 Ant Design Vue 组件库
app.use(Antd);

// 挂载 Vue 应用到 #app 元素
app.mount('#app');
