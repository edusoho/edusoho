<template>
  <div>
    <a-button type="primary">Ant Design Button</a-button>
  </div>
</template>

<script>
import { Button } from 'ant-design-vue';

export default {
  name: 'ManageInfo',
  components: {
    'a-button': Button,
  },
};
</script>

<style scoped>
/* 你的组件样式 */
</style>
